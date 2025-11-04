import sys
import json

print("Number of arguments:", len(sys.argv))
for i, arg in enumerate(sys.argv):
    print(f"Argument {i}: {repr(arg)}")

if len(sys.argv) > 2:
    print("\nAttempting to parse conversation state...")
    try:
        state = json.loads(sys.argv[2])
        print("Parsed state:", state)
    except Exception as e:
        print("Error parsing:", e)
