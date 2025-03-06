import sys
import requests
import json
from typing import Optional, Dict

class FacePlusPlusAgeDetector:
    def __init__(self, api_key: str, api_secret: str):
        self.api_key = api_key
        self.api_secret = api_secret
        self.base_url = "https://api-us.faceplusplus.com/facepp/v3/detect"

    def analyze_face(self, image_path: str) -> Optional[Dict]:
        try:
            files = {'image_file': open(image_path, 'rb')}
            data = {
                'api_key': self.api_key,
                'api_secret': self.api_secret,
                'return_attributes': 'age,gender'
            }
            response = requests.post(self.base_url, files=files, data=data)
            response.raise_for_status()
            return response.json()
        except Exception as e:
            print(f"Error: {e}", file=sys.stderr)
            return None

    def is_user_underage(self, image_path: str, age_threshold: int = 12) -> Dict:
        result = self.analyze_face(image_path)
        
        if not result or 'faces' not in result or not result['faces']:
            return {
                'allow_access': False,
                'estimated_age': None,
                'message': 'Aucun visage détecté'
            }
        
        age = result['faces'][0]['attributes']['age']['value']
        is_underage = age < age_threshold
        
        return {
            'allow_access': not is_underage,
            'estimated_age': age,
            'message': f"Accès refusé : Âge estimé {age} est inférieur à 12 ans" if is_underage else "Vérification d'âge réussie"
        }

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python script.py <image_path> <api_key> <api_secret>", file=sys.stderr)
        sys.exit(1)
    
    image_path, api_key, api_secret = sys.argv[1], sys.argv[2], sys.argv[3]
    detector = FacePlusPlusAgeDetector(api_key, api_secret)
    result = detector.is_user_underage(image_path)
    print(json.dumps(result))