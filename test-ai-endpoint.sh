#!/bin/bash
# Test the AI conversation endpoint

echo "Testing AI Conversation Endpoint..."
echo "===================================="
echo ""

# Test 1: Simple message
echo "Test 1: Sending 'Birthday party for 80 guests'"
curl -s -X POST http://localhost/Gatherly-EMS_2025/src/services/ai-conversation.php \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=test123" \
  -d '{"message":"Birthday party for 80 guests","conversation_state":{}}' \
  | python3 -m json.tool | head -20

echo ""
echo "===================================="
echo "Test complete!"
