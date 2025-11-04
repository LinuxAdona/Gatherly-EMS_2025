<?php
// Quick test for budget-only input

$pythonPath = 'C:/Python313/python.exe';
$pythonScript = __DIR__ . '/../ml/conversational_planner.py';

function escapeForWindows($str)
{
    return '"' . str_replace('"', '\\"', $str) . '"';
}

echo "Testing budget input with just a number:\n";
echo str_repeat("=", 60) . "\n\n";

// Simulate state where we're at the budget question
$state = [
    'event_type' => 'wedding',
    'guests' => 150,
    'services' => []
];

// Test 1: Just a plain number
echo "Test 1: User types '100000'\n";
$message = "100000";
$escapedMessage = escapeForWindows($message);
$stateJson = json_encode($state);
$stateArg = escapeForWindows($stateJson);
$command = "\"$pythonPath\" \"$pythonScript\" $escapedMessage $stateArg 2>&1";
$output = shell_exec($command);
$result = json_decode($output, true);

if ($result && $result['success']) {
    echo "✅ SUCCESS!\n";
    echo "   Stage: " . $result['stage'] . "\n";
    echo "   Budget extracted: " . ($result['conversation_state']['budget'] ?? 'NOT FOUND') . "\n";
    echo "   Response preview: " . substr($result['response'], 0, 100) . "...\n\n";
} else {
    echo "❌ FAILED\n";
    echo "   Error: " . ($result['error'] ?? 'Unknown') . "\n";
    echo "   Output: $output\n\n";
}

// Test 2: Number with peso
echo "Test 2: User types '50000 pesos'\n";
$message2 = "50000 pesos";
$escapedMessage2 = escapeForWindows($message2);
$command2 = "\"$pythonPath\" \"$pythonScript\" $escapedMessage2 $stateArg 2>&1";
$output2 = shell_exec($command2);
$result2 = json_decode($output2, true);

if ($result2 && $result2['success']) {
    echo "✅ SUCCESS!\n";
    echo "   Stage: " . $result2['stage'] . "\n";
    echo "   Budget extracted: " . ($result2['conversation_state']['budget'] ?? 'NOT FOUND') . "\n";
    echo "   Response preview: " . substr($result2['response'], 0, 100) . "...\n\n";
} else {
    echo "❌ FAILED\n";
    echo "   Error: " . ($result2['error'] ?? 'Unknown') . "\n";
    echo "   Output: $output2\n\n";
}

// Test 3: Number with comma
echo "Test 3: User types '75,000'\n";
$message3 = "75,000";
$escapedMessage3 = escapeForWindows($message3);
$command3 = "\"$pythonPath\" \"$pythonScript\" $escapedMessage3 $stateArg 2>&1";
$output3 = shell_exec($command3);
$result3 = json_decode($output3, true);

if ($result3 && $result3['success']) {
    echo "✅ SUCCESS!\n";
    echo "   Stage: " . $result3['stage'] . "\n";
    echo "   Budget extracted: " . ($result3['conversation_state']['budget'] ?? 'NOT FOUND') . "\n";
    echo "   Response preview: " . substr($result3['response'], 0, 100) . "...\n\n";
} else {
    echo "❌ FAILED\n";
    echo "   Error: " . ($result3['error'] ?? 'Unknown') . "\n";
    echo "   Output: $output3\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "Budget parsing tests completed!\n";
