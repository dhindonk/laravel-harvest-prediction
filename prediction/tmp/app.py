import os
import matplotlib
matplotlib.use('Agg')  # Use non-GUI backend to avoid errors
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
import pickle
import io
import base64
from datetime import datetime
import traceback
import tempfile
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
from sklearn.metrics import mean_squared_error, r2_score
from flask import Flask, request, jsonify
import time

app = Flask(__name__)

# Root endpoint for testing connectivity
@app.route('/')
def index():
    return jsonify({
        "status": "success",
        "message": "API prediksi hasil panen lidah buaya sedang berjalan",
        "version": "1.1.0"
    })

# Function to analyze trends from historical data
def analyze_trends(df):
    # Get the latest and earliest year from the data
    latest_year = df['Tahun'].max()
    earliest_year = df['Tahun'].min()
    years_diff = latest_year - earliest_year
    
    if years_diff == 0:
        years_diff = 1  # Avoid division by zero
    
    # Calculate trends for main numeric features
    trends = {}
    # Filter columns to analyze trends (only numeric columns)
    numeric_columns = df.select_dtypes(include=np.number).columns.tolist()
    columns_to_analyze = [col for col in numeric_columns if col not in ['Tahun', 'Bulan', 'Hasil_Panen_Kw']]
    
    # Group data by year to see trends
    yearly_data = df.groupby('Tahun')[columns_to_analyze].mean()
    
    for col in columns_to_analyze:
        if len(yearly_data) >= 2:
            # Calculate average annual change
            first_value = yearly_data[col].iloc[0]
            last_value = yearly_data[col].iloc[-1]
            
            # Avoid division by zero
            if first_value == 0:
                first_value = 0.001
                
            # Calculate percentage change per year
            change_rate = ((last_value - first_value) / first_value) / years_diff
            trends[col] = change_rate
        else:
            # If there's only one year of data, use default trend
            trends[col] = 0.01  # Default increase of 1% per year
    
    return trends

# Function to calculate seasonal patterns based on month
def get_seasonal_patterns(df):
    # Calculate averages by month to model seasonal patterns
    seasonal_patterns = {}
    
    # Get numeric columns
    numeric_columns = df.select_dtypes(include=np.number).columns.tolist()
    
    # Group by month and calculate mean for numeric columns only
    monthly_data = df.groupby('Bulan')[numeric_columns].mean()
    
    # Define seasonal columns (excluding certain columns)
    seasonal_columns = [col for col in numeric_columns if col not in ['Tahun', 'Bulan', 'Hasil_Panen_Kw']]
    
    for col in seasonal_columns:
        # Save monthly patterns
        seasonal_patterns[col] = monthly_data[col].to_dict()
    
    return seasonal_patterns

# New function to validate and sanitize input data
def validate_and_clean_data(df):
    """
    Validates and cleans the input DataFrame to ensure it has the correct format
    """
    try:
        print("Initial columns:", df.columns.tolist())
        
        # Check if the target column name is different and rename if needed
        target_column = 'Hasil_Panen_Kw'
        valid_target_columns = ['Hasil_Panen_Kw', 'Hasil_Panen (kwintal/ha)', 'Hasil_Panen']
        
        # Find which target column exists in the dataframe
        found_target = None
        for col in valid_target_columns:
            if col in df.columns:
                found_target = col
                break
        
        # If a valid target column was found but it's not the expected name, rename it
        if found_target and found_target != target_column:
            print(f"Renaming column from '{found_target}' to '{target_column}'")
            df = df.rename(columns={found_target: target_column})
        
        # Check if we found a valid target column
        if target_column not in df.columns:
            raise ValueError(f"Column for harvest result not found. Expected one of: {valid_target_columns}")
        
        # Validate minimum required columns
        required_columns = ['Tahun', 'Bulan', target_column]
        missing_columns = [col for col in required_columns if col not in df.columns]
        if missing_columns:
            raise ValueError(f"CSV missing essential columns: {missing_columns}")

        # Check if Lokasi column exists, if not add a default one
        has_location = 'Lokasi' in df.columns
        if not has_location:
            df['Lokasi'] = 'Default'
            print("Added default 'Lokasi' column")

        # Convert all numeric columns to their appropriate data types
        # Start with required columns
        for col in ['Tahun', 'Bulan', target_column]:
            df[col] = pd.to_numeric(df[col], errors='coerce')
        
        # Then try to convert all other columns (except 'Lokasi')
        for col in df.columns:
            if col != 'Lokasi':
                try:
                    df[col] = pd.to_numeric(df[col], errors='coerce')
                except:
                    pass  # If conversion fails, keep as is
        
        # Drop rows with NaN values in key columns
        df = df.dropna(subset=[target_column, 'Tahun', 'Bulan'])
        if len(df) == 0:
            raise ValueError('No valid data after filtering NaN values')
        
        return df
    except Exception as e:
        print(f"Error during data validation: {str(e)}")
        raise

# Function to select the best model between LinearRegression and RandomForest
def select_best_model(X, y):
    # Create model candidates
    models = {
        'LinearRegression': LinearRegression(),
        'RandomForest': RandomForestRegressor(n_estimators=100, random_state=42)
    }
    
    # Cross-validate to select the best model
    best_score = -float('inf')
    best_model_name = None
    
    for name, model in models.items():
        try:
            # Perform k-fold cross-validation
            cv_scores = cross_val_score(model, X, y, cv=min(5, len(X)), scoring='neg_mean_squared_error')
            avg_score = np.mean(cv_scores)
            
            print(f"Cross-validation score for {name}: {avg_score}")
            
            if avg_score > best_score:
                best_score = avg_score
                best_model_name = name
        except Exception as e:
            print(f"Error evaluating {name}: {str(e)}")
    
    # If no model could be evaluated, default to LinearRegression
    if best_model_name is None:
        best_model_name = 'LinearRegression'
        print("Defaulting to LinearRegression as no model could be properly evaluated")
    
    print(f"Selected model: {best_model_name}")
    return models[best_model_name]

# ----- Endpoint /predict -----
@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Check file upload
        if 'file' not in request.files:
            return jsonify({'error': 'No file part in the request'}), 400
        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'No selected file'}), 400
        
        print(f"Received file: {file.filename}, Content-Type: {file.content_type}")
        
        # Determine file type from content-type or extension
        is_excel = False
        if file.content_type in [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ] or file.filename.endswith(('.xlsx', '.xls')):
            is_excel = True
            print("Detected Excel file format")
        
        # Create a temporary file using tempfile module
        with tempfile.NamedTemporaryFile(delete=False, suffix=f".{file.filename.split('.')[-1]}") as temp_file:
            temp_file_path = temp_file.name
            file.save(temp_file_path)
            print(f"Saved temporary file at: {temp_file_path}")
        
        try:
            # Try to read based on file type
            if is_excel:
                try:
                    # Try to read Excel file
                    print("Reading as Excel file...")
                    df = pd.read_excel(temp_file_path, engine='openpyxl')
                    print(f"Successfully read Excel file. Shape: {df.shape}")
                    print(f"Columns detected: {df.columns.tolist()}")
                except Exception as e:
                    print(f"Error reading Excel file: {str(e)}")
                    # Try with alternative engine
                    try:
                        df = pd.read_excel(temp_file_path, engine='xlrd')
                        print(f"Successfully read Excel file with xlrd. Shape: {df.shape}")
                    except Exception as e2:
                        print(f"Error reading Excel file with xlrd: {str(e2)}")
                        raise ValueError(f"Failed to read Excel file: {str(e)}, {str(e2)}")
            else:
                # Try CSV with different encodings and delimiters
                print("Reading as CSV file...")
                encodings = ['utf-8', 'latin-1', 'iso-8859-1']
                for encoding in encodings:
                    try:
                        for delimiter in [',', ';', '\t']:
                            try:
                                df = pd.read_csv(temp_file_path, encoding=encoding, sep=delimiter)
                                if len(df.columns) > 1:
                                    print(f"Successfully read CSV with encoding {encoding} and delimiter '{delimiter}'")
                                    print(f"DataFrame shape: {df.shape}")
                                    break
                            except Exception as e:
                                print(f"Failed with delimiter '{delimiter}': {str(e)}")
                                continue
                        else:
                            # If all delimiters failed, continue to next encoding
                            continue
                        # If we reached here, we found a working combination
                        break
                    except Exception as e:
                        print(f"Failed with encoding {encoding}: {str(e)}")
                        continue
                else:
                    raise ValueError("Could not read CSV file with any standard encoding/delimiter.")
        except Exception as e:
            # Clean up temp file
            try:
                os.remove(temp_file_path)
            except:
                pass
            print(f"Error reading file: {str(e)}")
            return jsonify({'error': f"Unable to read uploaded file: {str(e)}"}), 400
        
        # Clean up temp file
        try:
            os.remove(temp_file_path)
        except Exception as e:
            print(f"Warning: Could not remove temp file: {str(e)}")
        
        # Print sample data for debugging
        print("\nData sample (first 5 rows):")
        print(df.head(5))
        
        # Validate and clean the data
        try:
            df = validate_and_clean_data(df)
            print(f"After validation, DataFrame shape: {df.shape}")
            print(f"Column types: {df.dtypes}")
        except ValueError as e:
            return jsonify({'error': str(e)}), 400
        
        # Categorize columns
        categorical_columns = ['Lokasi'] if 'Lokasi' in df.columns else []
        numeric_columns = [col for col in df.columns if col not in categorical_columns and col in df.select_dtypes(include=np.number).columns]
        
        print("Categorical columns:", categorical_columns)
        print("Numeric columns:", numeric_columns)
        
        # Target column
        target_column = 'Hasil_Panen_Kw'
        
        # Features and target
        X_columns = [col for col in df.columns if col != target_column]
        X = df[X_columns]
        y = df[target_column]
        
        # Ensure we have enough data
        if len(X) < 3:
            return jsonify({'error': 'Not enough data for training. Need at least 3 rows.'}), 400
        
        # Create preprocessing pipeline
        transformers = []
        if [col for col in numeric_columns if col in X.columns]:
            transformers.append(('num', StandardScaler(), [col for col in numeric_columns if col in X.columns]))
        if categorical_columns:
            transformers.append(('cat', OneHotEncoder(handle_unknown='ignore'), categorical_columns))
        
        preprocessor = ColumnTransformer(transformers=transformers)
        
        # Split data
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        # Select best model based on cross-validation
        best_model = select_best_model(preprocessor.fit_transform(X_train), y_train)
        
        # Create pipeline with preprocessor and selected model
        model_pipeline = Pipeline(steps=[
            ('preprocessor', preprocessor),
            ('regressor', best_model)
        ])
        
        # Fit model
        model_pipeline.fit(X_train, y_train)
        
        # Evaluate model
        train_score = model_pipeline.score(X_train, y_train)
        test_score = model_pipeline.score(X_test, y_test)
        
        print(f"Training R² score: {train_score:.4f}")
        print(f"Testing R² score: {test_score:.4f}")
        
        # ----- Predict for historical data (given data) -----
        pred_hist = model_pipeline.predict(X)
        pred_hist = np.round(pred_hist, 2)
        
        # Create time labels using both year and month
        timeLabelsHist = df['Tahun'].astype(str) + '-' + df['Bulan'].astype(str).str.zfill(2)
        if 'Lokasi' in df.columns:
            timeLabelsHist += '-' + df['Lokasi'].astype(str)
        
        # ----- Analyze trends and seasonal patterns from historical data -----
        trends = analyze_trends(df)
        seasonal_patterns = get_seasonal_patterns(df)
        
        # ----- Predict future (5 years ahead) -----
        last_year = df['Tahun'].max()
        
        # Create new DataFrame for future data: 5 years ahead of the last year
        future_data = []
        
        # Get unique month-location combinations from historical data
        if 'Lokasi' in df.columns:
            month_location_combinations = df[['Bulan', 'Lokasi']].drop_duplicates().values.tolist()
        else:
            month_location_combinations = [[month, 'Default'] for month in df['Bulan'].unique()]
        
        # If there aren't enough combinations, ensure all months are covered
        if len(month_location_combinations) < 12:
            unique_locations = df['Lokasi'].unique() if 'Lokasi' in df.columns else ['Default']
            all_month_location_combinations = []
            for month in range(1, 13):
                # Find existing entries for this month
                existing = [comb for comb in month_location_combinations if comb[0] == month]
                if existing:
                    all_month_location_combinations.extend(existing)
                else:
                    # If no entries for this month, use first location
                    all_month_location_combinations.append([month, unique_locations[0]])
            month_location_combinations = all_month_location_combinations
        
        # Identify all columns except target and those we'll generate specifically
        columns_to_project = [col for col in df.columns if col not in [target_column, 'Tahun', 'Bulan', 'Lokasi']]
        
        # Take reference data for each month-location combination
        for future_year in range(last_year + 1, last_year + 6):
            for bulan, lokasi in month_location_combinations:
                # Get reference from historical data for this month-location combination
                if 'Lokasi' in df.columns:
                    ref_data = df[(df['Bulan'] == bulan) & (df['Lokasi'] == lokasi)]
                else:
                    ref_data = df[df['Bulan'] == bulan]
                
                # If no reference data, use average for that month
                if len(ref_data) == 0:
                    ref_data = df[df['Bulan'] == bulan]
                
                # If still no data, use overall average
                if len(ref_data) == 0:
                    ref_data = df
                
                # Take most recent or average values as reference
                if len(ref_data) > 0:
                    # Try to get the most recent entry for this combination
                    ref_row = ref_data.loc[ref_data['Tahun'].idxmax()]
                else:
                    # Fall back to mean values
                    ref_row = df.mean()
                
                # Create new row for future data
                future_row = {
                    'Tahun': future_year,
                    'Bulan': bulan,
                    'Lokasi': lokasi,
                    target_column: 0  # Placeholder for prediction
                }
                
                # Calculate year difference for trend application
                year_diff = future_year - last_year
                
                # Add all other columns with trend projections
                for col in columns_to_project:
                    # Only apply trends to numeric columns
                    if col in numeric_columns and col in trends:
                        # Get base value from reference
                        base_value = ref_row[col]
                        
                        # Apply trend with small random variation (capped for stability)
                        trend_factor = min(max(trends.get(col, 0.01), -0.1), 0.1)  # Cap trends to prevent extreme values
                        projected_value = base_value * (1 + trend_factor * year_diff)
                        
                        # Add small random variation (±2%)
                        variation = 1 + np.random.uniform(-0.02, 0.02)
                        projected_value *= variation
                        
                        # Round to 2 decimal places
                        future_row[col] = round(projected_value, 2)
                    else:
                        # For categorical or non-trend columns, use as-is
                        future_row[col] = ref_row[col]
                
                future_data.append(future_row)
        
        # Create DataFrame from future data
        df_future = pd.DataFrame(future_data)
        
        # Ensure data types match original DataFrame
        for col in df_future.columns:
            if col in df.columns:
                df_future[col] = df_future[col].astype(df[col].dtype)
        
        # Predict for future data
        X_future = df_future[[col for col in df_future.columns if col != target_column]]
        pred_future = model_pipeline.predict(X_future)
        pred_future = np.round(pred_future, 2)
        
        # Create time labels for future predictions
        timeLabelsFuture = df_future['Tahun'].astype(str) + '-' + df_future['Bulan'].astype(str).str.zfill(2)
        if 'Lokasi' in df_future.columns:
            timeLabelsFuture += '-' + df_future['Lokasi'].astype(str)
        
        # ----- Yearly aggregation for historical data -----
        df_hist = df.copy()
        df_hist['Prediksi'] = pred_hist
        df_hist['Aktual'] = df[target_column]
        
        # Aggregation by Year and Location if Location exists
        if 'Lokasi' in df_hist.columns:
            group_year_location = df_hist.groupby(['Tahun', 'Lokasi'], as_index=False).agg({
                'Prediksi': 'mean',
                'Aktual': 'mean'
            })
            timeLabelsYearlyLocation = (group_year_location['Tahun'].astype(str) + 
                                        ' - ' + group_year_location['Lokasi'].astype(str)).tolist()
            predictionsYearlyLocation = np.round(group_year_location['Prediksi'].values, 2).tolist()
            actualYearlyLocation = np.round(group_year_location['Aktual'].values, 2).tolist()
        else:
            timeLabelsYearlyLocation = []
            predictionsYearlyLocation = []
            actualYearlyLocation = []
        
        # Aggregation only by Year
        group_year = df_hist.groupby('Tahun', as_index=False).agg({
            'Prediksi': 'mean',
            'Aktual': 'mean'
        })
        
        timeLabelsYearly = group_year['Tahun'].astype(str).tolist()
        predictionsYearly = np.round(group_year['Prediksi'].values, 2).tolist()
        actualYearly = np.round(group_year['Aktual'].values, 2).tolist()
        
        # ----- Yearly aggregation for future prediction data -----
        df_future_with_pred = df_future.copy()
        df_future_with_pred[target_column] = pred_future
        
        # Aggregation by Year and Location if Location exists
        if 'Lokasi' in df_future_with_pred.columns:
            future_yearly_location = df_future_with_pred.groupby(['Tahun', 'Lokasi'], as_index=False).agg({
                target_column: 'mean'
            })
            futureLabelsYearlyLocation = (future_yearly_location['Tahun'].astype(str) + 
                                        ' - ' + future_yearly_location['Lokasi'].astype(str)).tolist()
            futurePredictionsYearlyLocation = np.round(future_yearly_location[target_column].values, 2).tolist()
        else:
            futureLabelsYearlyLocation = []
            futurePredictionsYearlyLocation = []
        
        # Aggregation only by Year
        future_yearly = df_future_with_pred.groupby('Tahun', as_index=False).agg({
            target_column: 'mean'
        })
        
        futureYearsLabels = future_yearly['Tahun'].astype(str).tolist()
        futureYearlyPredictions = np.round(future_yearly[target_column].values, 2).tolist()
        
        # ----- Conclusion & Suggestion based on data and prediction -----
        # Extract feature importance
        importance_dict = {}
        
        # Get feature importance - different approach for different model types
        if isinstance(model_pipeline.named_steps['regressor'], RandomForestRegressor):
            # For Random Forest, we can get feature importance directly
            importances = model_pipeline.named_steps['regressor'].feature_importances_
            
            # Get feature names after preprocessing
            feature_names = []
            
            # Add numeric feature names
            numeric_features = [col for col in numeric_columns if col in X.columns]
            feature_names.extend(numeric_features)
            
            # Add categorical feature names after one-hot encoding
            if categorical_columns:
                try:
                    cat_encoder = preprocessor.transformers_[1][1]
                    if hasattr(cat_encoder, 'get_feature_names_out'):
                        cat_feature_names = cat_encoder.get_feature_names_out(categorical_columns)
                        feature_names.extend(cat_feature_names)
                except:
                    # Fallback if we can't get encoded feature names
                    for cat in categorical_columns:
                        feature_names.append(f"cat_{cat}")
            
            # Match importance to feature names when possible
            if len(feature_names) == len(importances):
                for name, importance in zip(feature_names, importances):
                    importance_dict[name] = importance
            else:
                # Fallback to using original feature names
                for i, col in enumerate(X.columns):
                    if i < len(importances):
                        importance_dict[col] = importances[i]
        
        else:
            # For Linear Regression, use coefficients
            regressor = model_pipeline.named_steps['regressor']
            
            # Get feature names after preprocessing (similar approach)
            feature_names = []
            
            # Add numeric feature names
            numeric_features = [col for col in numeric_columns if col in X.columns]
            feature_names.extend(numeric_features)
            
            # Add categorical feature names after one-hot encoding
            if categorical_columns:
                try:
                    cat_encoder = preprocessor.transformers_[1][1]
                    if hasattr(cat_encoder, 'get_feature_names_out'):
                        cat_feature_names = cat_encoder.get_feature_names_out(categorical_columns)
                        feature_names.extend(cat_feature_names)
                except:
                    # Fallback if we can't get encoded feature names
                    for cat in categorical_columns:
                        feature_names.append(f"cat_{cat}")
            
            # Match coefficients to feature names when possible
            if hasattr(regressor, 'coef_') and len(feature_names) == len(regressor.coef_):
                for name, coef in zip(feature_names, regressor.coef_):
                    importance_dict[name] = abs(coef)
            else:
                # Fallback: assign importance to original features
                for i, col in enumerate(X.columns):
                    importance_dict[col] = 1.0 / (i + 1)  # Arbitrary decreasing importance
        
        # Get top features
        sorted_features = sorted(importance_dict.items(), key=lambda x: x[1], reverse=True)
        top_features = [feat for feat, _ in sorted_features[:5]]
        
        # Format feature names for better display
        formatted_top_features = []
        for feat in top_features:
            if isinstance(feat, str):
                if feat.startswith('cat__'):
                    parts = feat.split('__')
                    if len(parts) > 1:
                        formatted_top_features.append(parts[-1])
                elif feat.startswith('x0_') or feat.startswith('x1_'):  # Another common prefix pattern
                    parts = feat.split('_')
                    if len(parts) > 1:
                        formatted_top_features.append('_'.join(parts[1:]))
                else:
                    formatted_top_features.append(feat)
        
        # Use formatted features if we have them, otherwise fallback to original
        if formatted_top_features:
            top_features = formatted_top_features[:5]
        
        # Check if any location is in important features
        location_important = any('Lokasi' in feat for feat in top_features[:3]) if categorical_columns else False
        
        # Analyze trends
        if len(futureYearlyPredictions) > 1:
            future_trend = "meningkat" if futureYearlyPredictions[-1] > futureYearlyPredictions[0] else "menurun"
            pct_change = abs(futureYearlyPredictions[-1] - futureYearlyPredictions[0]) / max(0.001, futureYearlyPredictions[0]) * 100
            
            max_year_idx = np.argmax(futureYearlyPredictions)
            min_year_idx = np.argmin(futureYearlyPredictions)
        else:
            # Fallback if we don't have enough future data
            future_trend = "stabil"
            pct_change = 0
            max_year_idx = 0
            min_year_idx = 0
        
        # Analyze best location if we have location data
        best_location = None
        if 'Lokasi' in df_future.columns:
            location_performance = df_future_with_pred.groupby('Lokasi')[target_column].mean()
            if not location_performance.empty:
                best_location = location_performance.idxmax()
        
        # Model evaluation metrics
        y_pred = model_pipeline.predict(X_test)
        rmse = np.sqrt(mean_squared_error(y_test, y_pred))
        r2 = r2_score(y_test, y_pred)
        
        # Create more specific and informative conclusion
        conclusion = (
            f"Analisis menunjukkan bahwa hasil panen lidah buaya diprediksi akan {future_trend} "
            f"sebesar {pct_change:.1f}% dalam 5 tahun ke depan. "
        )
        
        # Add information about model performance
        conclusion += f"Model prediksi memiliki akurasi R² sebesar {r2:.2f} dan RMSE {rmse:.2f} kwintal/ha. "
        
        # Add information about important features
        if len(top_features) >= 2:
            conclusion += f"Faktor {top_features[0]} dan {top_features[1]} adalah yang paling berpengaruh terhadap hasil panen. "
        
        if len(futureYearsLabels) > 1:
            conclusion += (
                f"Tahun {futureYearsLabels[max_year_idx]} diprediksi memberikan hasil tertinggi, "
                f"sementara tahun {futureYearsLabels[min_year_idx]} memberikan hasil terendah."
            )
        
        # Add location information if relevant
        if best_location:
            conclusion += f" Lokasi {best_location} menunjukkan performa terbaik berdasarkan prediksi."
        
        # Create suggestions based on the most important features
        suggestion = ""
        top_feature = top_features[0] if top_features else ""
        
        # Create suggestion based on top feature
        if top_feature:
            if 'Pupuk' in top_feature:
                suggestion = "Disarankan untuk mengoptimalkan dosis pupuk sesuai karakteristik lokasi, dengan peningkatan bertahap terutama menjelang tahun dengan prediksi hasil tertinggi."
            elif any(keyword in top_feature.lower() for keyword in ['curah', 'hujan', 'rainfall']):
                suggestion = "Pertimbangkan untuk mengembangkan sistem irigasi tambahan yang efisien, terutama pada bulan dengan curah hujan rendah dan untuk lokasi dengan karakteristik tanah yang spesifik."
            elif any(keyword in top_feature.lower() for keyword in ['suhu', 'temperature']):
                suggestion = "Pastikan kondisi suhu tetap stabil dengan teknik naungan parsial dan monitoring suhu berkala, sesuaikan dengan karakteristik masing-masing lokasi kebun."
            elif any(keyword in top_feature.lower() for keyword in ['lahan', 'area', 'luas']):
                suggestion = "Optimalkan penggunaan lahan melalui teknik penanaman yang efisien dan rotasi tanaman, dengan mempertimbangkan karakteristik unik setiap lokasi."
            elif any(keyword in top_feature.lower() for keyword in ['kelembapan', 'humidity']):
                suggestion = "Perhatikan tingkat kelembapan optimal untuk tanaman lidah buaya. Terapkan sistem pengaturan kelembapan yang tepat terutama di musim kemarau."
            elif any(keyword in top_feature.lower() for keyword in ['umur', 'age', 'tanaman']):
                suggestion = "Perhatikan umur tanaman lidah buaya yang optimal untuk panen. Lakukan penanaman secara bertahap untuk memastikan ketersediaan tanaman dengan umur ideal sepanjang tahun."
            elif 'Lokasi' in top_feature:
                suggestion = f"Lokasi sangat mempengaruhi hasil panen; pertimbangkan untuk mengalokasikan lebih banyak sumber daya ke lokasi dengan performa terbaik ({best_location if best_location else 'terbaik'}), dan terapkan praktik terbaik dari lokasi tersebut ke lokasi lainnya."
            else:
                suggestion = f"Faktor {top_feature} sangat mempengaruhi hasil panen; monitor dan optimalkan faktor ini secara teratur dengan mempertimbangkan variasi antar lokasi."
        else:
            suggestion = "Terapkan praktik terbaik dalam pemeliharaan tanaman lidah buaya seperti pengaturan jarak tanam yang tepat, pemupukan yang seimbang, dan pengendalian hama serta penyakit secara teratur."

        # Add additional recommendations
        suggestion += " Gunakan data prediksi ini untuk perencanaan jangka panjang dan alokasi sumber daya yang optimal."
        
        # Create detailed explanation for future predictions
        detailedExplanationFuture = ""
        if len(futureYearlyPredictions) >= 3:
            # Analyze year-over-year changes
            for i in range(1, len(futureYearlyPredictions)):
                prev_year = futureYearsLabels[i-1]
                curr_year = futureYearsLabels[i]
                prev_value = futureYearlyPredictions[i-1]
                curr_value = futureYearlyPredictions[i]
                
                if curr_value > prev_value:
                    pct_increase = ((curr_value - prev_value) / prev_value) * 100
                    detailedExplanationFuture += f"Pada tahun {curr_year}, hasil panen diprediksi meningkat sebesar {pct_increase:.1f}% dibandingkan tahun {prev_year}. "
                    
                    # Explain factors contributing to increase
                    if top_features:
                        top_feature_name = top_features[0]
                        if 'Pupuk' in top_feature_name:
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh optimasi dosis pupuk yang lebih baik. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['curah', 'hujan', 'rainfall']):
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh kondisi curah hujan yang lebih optimal. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['suhu', 'temperature']):
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh kondisi suhu yang lebih stabil. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['lahan', 'area', 'luas']):
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh optimasi penggunaan lahan yang lebih baik. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['kelembapan', 'humidity']):
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh pengaturan kelembapan yang optimal. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['umur', 'age', 'tanaman']):
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh pengaturan umur tanaman yang optimal. "
                        else:
                            detailedExplanationFuture += f"Peningkatan ini didukung oleh optimasi faktor {top_feature_name}. "
                else:
                    pct_decrease = ((prev_value - curr_value) / prev_value) * 100
                    detailedExplanationFuture += f"Pada tahun {curr_year}, hasil panen diprediksi menurun sebesar {pct_decrease:.1f}% dibandingkan tahun {prev_year}. "
                    
                    # Explain factors contributing to decrease
                    if top_features:
                        top_feature_name = top_features[0]
                        if 'Pupuk' in top_feature_name:
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh dosis pupuk yang tidak optimal. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['curah', 'hujan', 'rainfall']):
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh curah hujan yang tidak optimal. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['suhu', 'temperature']):
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh kondisi suhu yang tidak stabil. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['lahan', 'area', 'luas']):
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh penggunaan lahan yang tidak optimal. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['kelembapan', 'humidity']):
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh tingkat kelembapan yang tidak optimal. "
                        elif any(keyword in top_feature_name.lower() for keyword in ['umur', 'age', 'tanaman']):
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh umur tanaman yang tidak optimal. "
                        else:
                            detailedExplanationFuture += f"Penurunan ini kemungkinan disebabkan oleh penurunan faktor {top_feature_name}. "

        # Prepare response with all the analysis results
        response = {
            'status': 'success',
            'data': {
                'predictionsMonthlyHistorical': pred_hist.tolist(),
                'timeLabelsMonthlyHistorical': timeLabelsHist.tolist(),
                'predictionsMonthlyFuture': pred_future.tolist(),
                'timeLabelsMonthlyFuture': timeLabelsFuture.tolist(),
                'predictionsYearlyHistorical': predictionsYearly,
                'timeLabelsYearlyHistorical': timeLabelsYearly,
                'actualYearlyHistorical': actualYearly,
                'predictionsYearlyLocationHistorical': predictionsYearlyLocation,
                'timeLabelsYearlyLocationHistorical': timeLabelsYearlyLocation,
                'predictionsYearlyLocationFuture': futurePredictionsYearlyLocation,
                'timeLabelsYearlyLocationFuture': futureLabelsYearlyLocation,
                'timeLabelsYearlyFuture': futureYearsLabels,
                'predictionsYearlyFuture': futureYearlyPredictions,
                'conclusion': conclusion,
                'suggestion': suggestion,
                'detailedExplanationFuture': detailedExplanationFuture,
                'modelPerformance': {
                    'r2_score': r2,
                    'rmse': rmse
                }
            }
        }
        return jsonify(response)
    except Exception as e:
        print(f"Error in prediction: {str(e)}")
        traceback.print_exc()
        return jsonify({'status': 'error', 'message': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0')
