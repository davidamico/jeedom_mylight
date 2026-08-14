# Plugin Jeedom - MyLight150 ☀️🔋

Plugin natif pour Jeedom permettant de récupérer et surveiller les données de votre installation solaire MyLight Systems. 

Ce plugin gère de manière transparente la nouvelle plateforme d'authentification sécurisée (Microsoft Azure AD B2C / PKCE) et communique directement avec l'API REST officielle de MyLight (`client.mylight150.com`).

## 🌟 Fonctionnalités

Une fois configuré, le plugin génère automatiquement les commandes suivantes pour votre installation :

* **Maison en direct** : Puissance solaire produite, consommation de la maison (Load), et tirage/injection sur le réseau (Grid).
* **Batterie Virtuelle (MSB)** : État actuel de la batterie (charge/décharge/idle), puissance instantanée, autonomie restante (kWh), capacité totale et niveau de charge (%).
* **Cagnotte (Money Pot)** : Suivi en temps réel du solde de votre cagnotte (en €).
* **Actualisation automatique** : Les données sont rafraîchies automatiquement via le système de Cron de Jeedom (toutes les heures par défaut).

## 🚀 Installation

### Option 1 : Depuis un fichier ZIP (Installation manuelle)
1. Téléchargez la dernière version du plugin au format `.zip` depuis la page [Releases](../../releases) de ce dépôt.
2. Dans votre Jeedom, assurez-vous d'avoir activé l'installation depuis une source externe ( *Réglages > Système > Configuration > Mises à jour/Market > Activer l'envoi direct de zip* ).
3. Allez dans **Réglages > Système > Gestion des plugins**.
4. Cliquez sur l'icône **+** (Ajouter depuis fichier/zip) et uploadez l'archive.
5. Activez le plugin.

### Option 2 : Depuis le Market Jeedom
*(À venir - Sous réserve de validation sur le Market Jeedom)*

## ⚙️ Configuration

1. Rendez-vous dans la page de configuration globale du plugin MyLight150.
2. Renseignez simplement vos identifiants de l'application MyLight :
   * **Email** : Votre identifiant de connexion MyLight
   * **Mot de passe** : Votre mot de passe
3. Sauvegardez.
4. Allez dans **Plugins > Énergie > MyLight150** et ajoutez un nouvel équipement. 
5. Cochez "Activer" et "Visible", puis cliquez sur **Sauvegarder**. Toutes vos commandes se créeront automatiquement !

## 🤝 Remerciements & Crédits

L'architecture de communication avec l'API et le mécanisme complexe d'authentification OAuth2/PKCE de ce plugin Jeedom ont été inspirés et adaptés du travail exceptionnel réalisé sur le composant Home Assistant par [Racailloux/mylight150_ha](https://github.com/Racailloux/mylight150_ha). Un immense merci à lui pour la rétro-ingénierie de l'API MyLight !

## 📝 Licence

Ce projet est sous licence **AGPL-3.0**, garantissant qu'il reste open-source et parfaitement compatible avec la philosophie et le Core de Jeedom.
