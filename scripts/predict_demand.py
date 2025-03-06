import pandas as pd
from statsmodels.tsa.arima.model import ARIMA
import json
import requests

def predict_demand_for_all_products(stock_data):
    # Convertir les données de stock en DataFrame pandas
    data = pd.DataFrame(stock_data)

    # Vérifier si les colonnes nécessaires sont présentes
    if 'product_id' not in data.columns or 'quantity' not in data.columns or 'date' not in data.columns:
        return {"error": "Les colonnes 'product_id', 'quantity' et 'date' sont requises."}

    # Convertir la colonne 'date' en datetime
    data['date'] = pd.to_datetime(data['date'])

    # Liste des produits uniques
    product_ids = data['product_id'].unique()

    # Dictionnaire pour stocker les prédictions
    predictions = {}

    # Prédire la demande pour chaque produit
    for product_id in product_ids:
        # Filtrer les données pour le produit spécifié
        product_data = data[data['product_id'] == product_id]

        # Vérifier si les données sont suffisantes
        if len(product_data) < 10:  # Au moins 10 observations
            predictions[product_id] = {"error": "Not enough data to train the model"}
            continue

        # Agrégation des données par jour
        daily_sales = product_data.resample('D', on='date').sum()

        # Entraînement du modèle ARIMA
        try:
            model = ARIMA(daily_sales['quantity'], order=(1, 1, 1))  # Ordre simplifié
            model_fit = model.fit()
        except Exception as e:
            predictions[product_id] = {"error": str(e)}
            continue

        # Prédiction pour les 7 prochains jours
        forecast = model_fit.forecast(steps=7)

        # Convertir les résultats en format JSON
        forecast_dict = {date.strftime('%Y-%m-%d'): round(quantity, 2) for date, quantity in forecast.items()}
        predictions[product_id] = forecast_dict
        url = "http://example.com/api/stock/predictions"
    headers = {'Content-Type': 'application/json'}
    response = requests.post(url, headers=headers, data=json.dumps(predictions))

    if response.status_code == 200:
        print("Prédictions envoyées avec succès.")
    else:
        print("Erreur lors de l'envoi des prédictions.")

    return predictions