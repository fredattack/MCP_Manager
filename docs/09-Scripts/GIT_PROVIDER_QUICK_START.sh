#!/bin/bash

# Git Provider Service - Quick Start Script
# ==========================================

set -e

API_URL="${API_URL:-http://localhost:3978/api}"
TOKEN="${API_TOKEN:-your_api_token_here}"

echo "🚀 Git Provider Service - Quick Start"
echo "======================================"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if API token is set
if [ "$TOKEN" = "your_api_token_here" ]; then
    echo -e "${YELLOW}⚠️  Warning: API_TOKEN not set${NC}"
    echo "Usage: API_TOKEN=your_token ./GIT_PROVIDER_QUICK_START.sh"
    exit 1
fi

# Function to make API requests
api_request() {
    local method=$1
    local endpoint=$2
    local data=$3

    if [ -n "$data" ]; then
        curl -s -X "$method" "$API_URL$endpoint" \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            -d "$data"
    else
        curl -s -X "$method" "$API_URL$endpoint" \
            -H "Authorization: Bearer $TOKEN"
    fi
}

# 1. Start OAuth
echo -e "${BLUE}📝 Step 1: Starting OAuth flow...${NC}"
AUTH_RESPONSE=$(api_request POST "/git/github/oauth/start")

if echo "$AUTH_RESPONSE" | jq -e '.auth_url' > /dev/null 2>&1; then
    AUTH_URL=$(echo "$AUTH_RESPONSE" | jq -r '.auth_url')
    echo -e "${GREEN}✓ OAuth URL generated${NC}"
    echo ""
    echo "Please open this URL in your browser:"
    echo "$AUTH_URL"
    echo ""
    echo "After authorization, you will be redirected to the callback URL."
    read -p "Press Enter once you've completed the OAuth flow..."
else
    echo -e "${YELLOW}⚠️  OAuth start failed${NC}"
    echo "$AUTH_RESPONSE" | jq '.'
    exit 1
fi

# 2. Sync Repositories
echo ""
echo -e "${BLUE}📦 Step 2: Synchronizing repositories...${NC}"
SYNC_RESPONSE=$(api_request POST "/git/github/repos/sync")

if echo "$SYNC_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
    SYNCED=$(echo "$SYNC_RESPONSE" | jq -r '.synced')
    CREATED=$(echo "$SYNC_RESPONSE" | jq -r '.created')
    UPDATED=$(echo "$SYNC_RESPONSE" | jq -r '.updated')
    DURATION=$(echo "$SYNC_RESPONSE" | jq -r '.duration_ms')

    echo -e "${GREEN}✓ Sync completed${NC}"
    echo "  - Total synced: $SYNCED"
    echo "  - Created: $CREATED"
    echo "  - Updated: $UPDATED"
    echo "  - Duration: ${DURATION}ms"
else
    echo -e "${YELLOW}⚠️  Sync failed${NC}"
    echo "$SYNC_RESPONSE" | jq '.'
    exit 1
fi

# 3. List Repositories
echo ""
echo -e "${BLUE}📋 Step 3: Listing repositories...${NC}"
REPOS_RESPONSE=$(api_request GET "/git/github/repos?per_page=5")

if echo "$REPOS_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Repositories fetched${NC}"
    echo ""
    echo "Top 5 repositories:"
    echo "$REPOS_RESPONSE" | jq -r '.data[] | "  - \(.full_name) (\(.visibility)) - \(.meta.language // "N/A")"'

    # Save first repo for cloning
    FIRST_REPO_ID=$(echo "$REPOS_RESPONSE" | jq -r '.data[0].external_id')
    FIRST_REPO_NAME=$(echo "$REPOS_RESPONSE" | jq -r '.data[0].full_name')
else
    echo -e "${YELLOW}⚠️  Listing failed${NC}"
    echo "$REPOS_RESPONSE" | jq '.'
    exit 1
fi

# 4. Get Statistics
echo ""
echo -e "${BLUE}📊 Step 4: Getting repository statistics...${NC}"
STATS_RESPONSE=$(api_request GET "/git/github/repos/stats")

if echo "$STATS_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
    TOTAL=$(echo "$STATS_RESPONSE" | jq -r '.stats.total')
    PRIVATE=$(echo "$STATS_RESPONSE" | jq -r '.stats.private')
    PUBLIC=$(echo "$STATS_RESPONSE" | jq -r '.stats.public')
    ARCHIVED=$(echo "$STATS_RESPONSE" | jq -r '.stats.archived')

    echo -e "${GREEN}✓ Statistics retrieved${NC}"
    echo "  - Total: $TOTAL"
    echo "  - Private: $PRIVATE"
    echo "  - Public: $PUBLIC"
    echo "  - Archived: $ARCHIVED"
fi

# 5. Clone Repository
echo ""
echo -e "${BLUE}📥 Step 5: Cloning repository '$FIRST_REPO_NAME'...${NC}"

if [ -z "$FIRST_REPO_ID" ]; then
    echo -e "${YELLOW}⚠️  No repository to clone${NC}"
    exit 0
fi

CLONE_DATA='{"ref":"main","storage":"local"}'
CLONE_RESPONSE=$(api_request POST "/git/github/repos/$FIRST_REPO_ID/clone" "$CLONE_DATA")

if echo "$CLONE_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
    CLONE_ID=$(echo "$CLONE_RESPONSE" | jq -r '.clone.id')
    CLONE_STATUS=$(echo "$CLONE_RESPONSE" | jq -r '.clone.status')

    echo -e "${GREEN}✓ Clone job dispatched${NC}"
    echo "  - Clone ID: $CLONE_ID"
    echo "  - Repository: $FIRST_REPO_NAME"
    echo "  - Status: $CLONE_STATUS"

    # 6. Poll Clone Status
    echo ""
    echo -e "${BLUE}⏳ Step 6: Monitoring clone progress...${NC}"
    echo "(This may take up to 60 seconds)"

    for i in {1..12}; do
        sleep 5
        CLONE_STATUS_RESPONSE=$(api_request GET "/git/clones/$CLONE_ID")

        if echo "$CLONE_STATUS_RESPONSE" | jq -e '.success' > /dev/null 2>&1; then
            STATUS=$(echo "$CLONE_STATUS_RESPONSE" | jq -r '.data.status')
            echo -e "  [$i] Status: $STATUS"

            if [ "$STATUS" = "completed" ]; then
                SIZE=$(echo "$CLONE_STATUS_RESPONSE" | jq -r '.data.size_formatted')
                DURATION=$(echo "$CLONE_STATUS_RESPONSE" | jq -r '.data.duration_formatted')

                echo ""
                echo -e "${GREEN}✓ Clone completed successfully!${NC}"
                echo "  - Size: $SIZE"
                echo "  - Duration: $DURATION"
                echo "  - Path: $(echo "$CLONE_STATUS_RESPONSE" | jq -r '.data.artifact_path')"
                break
            elif [ "$STATUS" = "failed" ]; then
                ERROR=$(echo "$CLONE_STATUS_RESPONSE" | jq -r '.data.error')
                echo ""
                echo -e "${YELLOW}⚠️  Clone failed: $ERROR${NC}"
                break
            fi
        fi

        if [ $i -eq 12 ]; then
            echo ""
            echo -e "${YELLOW}⚠️  Clone still in progress after 60s${NC}"
            echo "Check status later with:"
            echo "  curl -X GET $API_URL/git/clones/$CLONE_ID -H 'Authorization: Bearer $TOKEN'"
        fi
    done
else
    echo -e "${YELLOW}⚠️  Clone initiation failed${NC}"
    echo "$CLONE_RESPONSE" | jq '.'
fi

# Summary
echo ""
echo -e "${GREEN}🎉 Quick start completed!${NC}"
echo ""
echo "Next steps:"
echo "  - View all repositories: GET $API_URL/git/github/repos"
echo "  - Clone another repo: POST $API_URL/git/github/repos/{id}/clone"
echo "  - Check clone status: GET $API_URL/git/clones/{id}"
echo ""
echo "Documentation:"
echo "  - API Guide: GIT_PROVIDER_API_GUIDE.md"
echo "  - README: GIT_PROVIDER_README.md"
echo "  - Summary: GIT_PROVIDER_IMPLEMENTATION_SUMMARY.md"
