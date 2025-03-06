import sys
import pandas as pd
from transformers import T5ForConditionalGeneration, T5Tokenizer
from sklearn.model_selection import train_test_split

# Charger les données
data = pd.read_csv('donnees.csv')

# Préparation des données d'entraînement
prompts = data['prompt']  # Si tu as une colonne pour les prompts
responses = data['response']  # Si tu as une colonne pour les réponses

# Split des données en entraînement et validation
train_prompts, val_prompts, train_responses, val_responses = train_test_split(prompts, responses, test_size=0.2, random_state=42)

# Charger le modèle et le tokenizer
model = T5ForConditionalGeneration.from_pretrained('t5-base')
tokenizer = T5Tokenizer.from_pretrained('t5-base')

# Fonction pour préparer les données
def prepare_data(prompts, responses):
    inputs = tokenizer(prompts, return_tensors="pt", padding=True, truncation=True)
    labels = tokenizer(responses, return_tensors="pt", padding=True, truncation=True)
    return inputs, labels

# Préparation des données d'entraînement et de validation
train_inputs, train_labels = prepare_data(train_prompts, train_responses)
val_inputs, val_labels = prepare_data(val_prompts, val_responses)

# Entraînement du modèle
from torch.utils.data import Dataset, DataLoader
import torch

class StockDataset(Dataset):
    def __init__(self, inputs, labels):
        self.inputs = inputs
        self.labels = labels

    def __len__(self):
        return len(self.inputs['input_ids'])

    def __getitem__(self, idx):
        return {
            'input_ids': self.inputs['input_ids'][idx],
            'attention_mask': self.inputs['attention_mask'][idx],
            'labels': self.labels['input_ids'][idx]
        }

train_dataset = StockDataset(train_inputs, train_labels)
val_dataset = StockDataset(val_inputs, val_labels)

train_loader = DataLoader(train_dataset, batch_size=16, shuffle=True)
val_loader = DataLoader(val_dataset, batch_size=16)

device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
model.to(device)

# Boucle d'entraînement
for epoch in range(5):
    model.train()
    total_loss = 0
    for batch in train_loader:
        input_ids = batch['input_ids'].to(device)
        attention_mask = batch['attention_mask'].to(device)
        labels = batch['labels'].to(device)

        optimizer = torch.optim.Adam(model.parameters(), lr=1e-5)

        optimizer.zero_grad()

        outputs = model(input_ids, attention_mask=attention_mask, labels=labels)
        loss = outputs.loss

        loss.backward()
        optimizer.step()

        total_loss += loss.item()
    print(f'Epoch {epoch+1}, Loss: {total_loss / len(train_loader)}')

    model.eval()
    with torch.no_grad():
        total_loss = 0
        for batch in val_loader:
            input_ids = batch['input_ids'].to(device)
            attention_mask = batch['attention_mask'].to(device)
            labels = batch['labels'].to(device)

            outputs = model(input_ids, attention_mask=attention_mask, labels=labels)
            loss = outputs.loss
            total_loss += loss.item()
        print(f'Epoch {epoch+1}, Validation Loss: {total_loss / len(val_loader)}')

# Utilisation du modèle entraîné pour générer des rapports
def generate_report(prompt):
    inputs = tokenizer(prompt, return_tensors="pt")
    outputs = model.generate(inputs['input_ids'], max_length=200)
    report = tokenizer.decode(outputs[0], skip_special_tokens=True)
    return report

if __name__ == "__main__":
    prompt = sys.argv[1]
    report = generate_report(prompt)
    print(report)
