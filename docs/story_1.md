
   A. Nouveau service : NotionService.php
   Appel REST avec le token JWT vers /notion/fetch.

Utilisation de Http::withToken()->get(...) avec MCP_SERVER_URL issu du .env.

B. Nouveau contrôleur : NotionController
Route API interne /api/notion/fetch.

Injecte NotionService, vérifie les erreurs et retourne le JSON tel quel.

C. Authentification
Générer ou transmettre un token JWT depuis Laravel.

Stocker le token MCP dans .env ou le récupérer via un service d’auth (selon future stratégie).

3. Côté MCP Manager (React frontend)
   A. Composant : NotionFetcher
   Bouton “Fetch Notion Pages”

Affichage des résultats en liste (titre, ID, URL)

États : loading, success, error

B. API call
Appel GET /api/notion/fetch depuis React.

Affichage des erreurs côté utilisateur si la réponse échoue.

🧱 Responsabilités des composants
Composant	Rôle
MCP Server /notion/fetch	Récupérer les pages Notion à l’aide du token API
Laravel NotionService	Orchestrer l’appel vers le MCP Server
Laravel NotionController	Exposer une API sécurisée au frontend
React NotionFetcher	Permettre à l’utilisateur de lancer la requête et voir les résultats

🔗 Dépendances et configuration
.env dans le manager :

MCP_SERVER_URL=http://mcp_server:8000

MCP_API_TOKEN=xxxxxx

Paquets Laravel : guzzlehttp/guzzle, ou Illuminate\Http\Client

React : aucune dépendance supplémentaire requise pour un simple fetch.

✅ Validations techniques
Le serveur MCP répond bien à la route /notion/fetch

Le manager transmet correctement le JWT

Le format de réponse est structuré (list d’objets Notion)

La réponse est affichée correctement en frontend

🧪 Tests à effectuer
Côté serveur MCP
Test unitaire sur la route /notion/fetch avec un token valide

Test d’intégration avec token invalide ou manquant

Côté Laravel
Test du service NotionService

Test d’intégration de l’API /api/notion/fetch

Côté React
Test d’affichage conditionnel (chargement, succès, échec)

Vérification du rendu des données Notion

⚠️ Points d’attention
Vérifier que le token JWT utilisé par le manager est bien reconnu par le MCP Server

Bien isoler les erreurs réseau côté backend Laravel pour éviter de faire tomber le frontend

Gérer proprement les CORS entre le manager et le MCP Server si testés sur des ports différents en local

Assurer que l’appel /notion/fetch n’exige pas d'autres modules supprimés dans le simplify_plan

