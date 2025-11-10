#!/bin/bash
echo "========================================"
echo "Test Scenario 1: Budget Birthday Party"
echo "50 guests, ₱20,000 budget"
echo "========================================"
./venv/bin/python3 conversational_planner.py "all" '{"event_type":"birthday","guests":50,"budget":20000,"date_mentioned":true}' | python3 -c "
import sys, json
data = json.load(sys.stdin)
if 'venues' in data:
    print(f\"Found {len(data['venues'])} venues:\")
    for v in data['venues']:
        print(f\"  • {v['name']}: {v['score']}% match (₱{v['price']:,.0f}, capacity: {v['capacity']})\")
"

echo ""
echo "========================================"
echo "Test Scenario 2: Large Corporate Event"
echo "200 guests, ₱150,000 budget"
echo "========================================"
./venv/bin/python3 conversational_planner.py "all" '{"event_type":"corporate","guests":200,"budget":150000,"date_mentioned":true}' | python3 -c "
import sys, json
data = json.load(sys.stdin)
if 'venues' in data:
    print(f\"Found {len(data['venues'])} venues:\")
    for v in data['venues']:
        print(f\"  • {v['name']}: {v['score']}% match (₱{v['price']:,.0f}, capacity: {v['capacity']})\")
"

echo ""
echo "========================================"
echo "Test Scenario 3: Medium Wedding"
echo "120 guests, ₱80,000 budget"
echo "========================================"
./venv/bin/python3 conversational_planner.py "all" '{"event_type":"wedding","guests":120,"budget":80000,"date_mentioned":true}' | python3 -c "
import sys, json
data = json.load(sys.stdin)
if 'venues' in data:
    print(f\"Found {len(data['venues'])} venues:\")
    for v in data['venues']:
        print(f\"  • {v['name']}: {v['score']}% match (₱{v['price']:,.0f}, capacity: {v['capacity']})\")
"
