#!/usr/bin/env python3
"""
Test script to verify ML-enhanced venue scoring
"""

import json
import subprocess
import sys

def test_conversation():
    """Test the full conversation flow"""
    
    conversations = [
        ("Hello, I need help planning an event", {}),
        ("Birthday party", {"services": []}),
        ("50 guests", {"event_type": "birthday", "services": []}),
        ("20000", {"event_type": "birthday", "guests": 50, "services": []}),
        ("all services", {"event_type": "birthday", "guests": 50, "budget": 20000, "services": []})
    ]
    
    for i, (message, state) in enumerate(conversations):
        print(f"\n{'='*60}")
        print(f"Step {i+1}: {message}")
        print(f"State: {state}")
        print(f"{'='*60}")
        
        # Build command
        cmd = ["./venv/bin/python3", "conversational_planner.py", message]
        if state:
            cmd.append(json.dumps(state))
        
        # Execute
        result = subprocess.run(cmd, capture_output=True, text=True)
        
        if result.returncode != 0:
            print(f"ERROR: {result.stderr}")
            return False
        
        # Parse response
        try:
            response = json.loads(result.stdout)
            print(f"\nResponse: {response.get('response', 'N/A')[:200]}...")
            print(f"Stage: {response.get('stage', 'N/A')}")
            print(f"Needs more info: {response.get('needs_more_info', 'N/A')}")
            
            if response.get('venues'):
                print(f"\nVENUES FOUND: {len(response['venues'])}")
                for venue in response['venues']:
                    print(f"  - {venue['name']}: {venue['score']}% match")
                    print(f"    Capacity: {venue['capacity']}, Price: ₱{venue['price']:,.2f}")
                    if venue.get('ml_confidence'):
                        print(f"    ML Confidence: {venue['ml_confidence']}%")
            
            # Update state for next iteration
            if response.get('conversation_state'):
                state = response['conversation_state']
            
        except json.JSONDecodeError as e:
            print(f"JSON Parse Error: {e}")
            print(f"Raw output: {result.stdout}")
            return False
    
    print(f"\n{'='*60}")
    print("All tests completed!")
    print(f"{'='*60}")
    return True

if __name__ == '__main__':
    success = test_conversation()
    sys.exit(0 if success else 1)
