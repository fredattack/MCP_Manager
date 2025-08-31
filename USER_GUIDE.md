
# Guide Utilisateur - MCP Manager

## Bienvenue dans MCP Manager

MCP Manager est une application web qui vous permet de gérer vos intégrations avec différents services via le protocole MCP (Model Context Protocol). L'application prend en charge plusieurs intégrations : Notion, Todoist, Gmail, Google Calendar et JIRA.

## Démarrage Rapide

### 1. Connexion à l'Application

1. Accédez à l'application via votre navigateur
2. Créez un compte ou connectez-vous avec vos identifiants existants
3. Vous serez redirigé vers le tableau de bord principal

### 2. Configuration des Intégrations

Pour configurer une intégration :

1. Dans la barre latérale, cliquez sur **"Integrations"**
2. Choisissez le service que vous souhaitez connecter
3. Cliquez sur le bouton **"Connect"**
4. Suivez les instructions d'autorisation du service
5. Une fois connecté, le statut passera à **"Active"**

#### Vérification du Statut

- Un badge vert **"Active"** indique que votre intégration fonctionne correctement
- Un badge rouge **"Inactive"** signifie que l'intégration nécessite une reconnexion

## 3. Utilisation des Intégrations

### Notion

#### Recherche dans Notion
1. Cliquez sur **"Notion"** dans la barre latérale
2. Utilisez la barre de recherche pour trouver du contenu
3. Les résultats s'affichent en temps réel
4. Cliquez sur un résultat pour voir plus de détails

### Todoist

#### Gestion des Tâches
1. Accédez à **"Todoist"** via Integrations
2. Visualisez vos projets et tâches
3. Créez de nouvelles tâches
4. Marquez les tâches comme complétées
5. Organisez vos tâches par projets et labels

### Gmail

#### Consultation des Emails
1. Cliquez sur **"Gmail"** dans la barre latérale
2. Parcourez votre boîte de réception
3. Lisez vos emails directement dans l'interface
4. Effectuez des recherches dans vos messages

### Google Calendar

#### Gestion du Calendrier
1. Accédez à **"Calendar"** dans la barre latérale
2. Visualisez vos événements
3. Naviguez entre les différentes vues (jour, semaine, mois)
4. Consultez les détails de vos rendez-vous

### JIRA

#### Gestion de Projets
1. Cliquez sur **"JIRA"** dans la barre latérale
2. Parcourez vos projets et tableaux
3. Visualisez et gérez vos issues
4. Suivez l'avancement des sprints
5. Créez et mettez à jour des tickets

## 4. Fonctionnalités Avancées

### AI Chat

L'application intègre une interface de chat avec Claude AI :

1. Accédez à **"AI Chat"** dans la barre latérale
2. Posez vos questions ou demandez de l'aide
3. L'IA peut vous assister dans vos tâches quotidiennes
4. L'historique de conversation est conservé

### Planification Quotidienne

1. Utilisez **"Daily Planning"** pour organiser votre journée
2. Intégrez vos tâches de différentes sources
3. Priorisez et planifiez efficacement

### Commandes en Langage Naturel

1. Accédez à **"Natural Language"**
2. Donnez des instructions en langage naturel
3. L'application exécutera les actions correspondantes

## 5. Gestion de Votre Compte

#### Profil Utilisateur

1. Cliquez sur votre avatar en haut à droite
2. Sélectionnez **"Settings"** pour accéder à vos paramètres
3. Vous pouvez y modifier :
   - Votre nom
   - Votre adresse email
   - Votre mot de passe

#### Déconnexion d'une Intégration

1. Allez dans **"Integrations"**
2. Sur la carte Notion, cliquez sur **"Disconnect"**
3. Confirmez la déconnexion

## 6. Tableau de Bord

Le tableau de bord vous donne un aperçu rapide de :
- L'état de vos intégrations actives
- Un accès rapide aux fonctionnalités principales
- Les notifications importantes (le cas échéant)
- Un résumé de vos tâches et événements à venir

## Résolution des Problèmes

### Une intégration ne fonctionne plus

1. Vérifiez le statut sur la page Integrations
2. Si le statut est "Inactive", cliquez sur "Reconnect"
3. Autorisez à nouveau l'accès au service concerné

### La recherche ne retourne aucun résultat

- Vérifiez que l'intégration concernée est active
- Assurez-vous d'avoir du contenu dans le service
- Essayez des termes de recherche plus généraux
- Pour Gmail, vérifiez les permissions d'accès

### Problèmes de connexion

- Vérifiez vos identifiants
- Utilisez la fonction "Mot de passe oublié" si nécessaire
- Contactez le support si le problème persiste

### Problèmes avec JIRA

- Vérifiez que vous avez les permissions nécessaires dans JIRA
- Assurez-vous que l'URL de votre instance JIRA est correcte
- Vérifiez que votre token API est valide

### Problèmes avec Todoist

- Assurez-vous que votre compte Todoist est actif
- Vérifiez les permissions accordées à l'application
- Essayez de vous reconnecter si les tâches ne se synchronisent pas

## Sécurité

- Vos tokens d'accès sont chiffrés et stockés de manière sécurisée
- Vous pouvez révoquer l'accès à tout moment depuis la page Integrations
- L'application utilise HTTPS pour toutes les communications
- Les données sensibles ne sont jamais exposées dans les logs
- Chaque intégration a des permissions limitées au strict nécessaire

## Limites et Considérations

- **Gmail** : Limité à la lecture des emails, pas d'envoi pour le moment
- **Google Calendar** : Consultation uniquement, pas de création d'événements
- **JIRA** : Nécessite des permissions appropriées dans votre instance
- **Todoist** : Toutes les fonctionnalités sont disponibles via l'interface MCP

## Support

Pour toute question ou problème non résolu, n'hésitez pas à contacter notre équipe de support via l'interface de l'application.

## Raccourcis Utiles

- **Ctrl/Cmd + K** : Recherche rapide (sur certaines pages)
- **Esc** : Fermer les modales et dialogues
- Navigation au clavier dans les listes et tableaux
