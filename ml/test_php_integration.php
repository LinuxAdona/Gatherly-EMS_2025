<?php
// Test the AI conversation API with proper state handling

$pythonPath = 'C:/Python313/python.exe';
$pythonScript = __DIR__ . '/../ml/conversational_planner.py';

// Windows-specific escaping function
function escapeForWindows($str)
{
    // Escape double quotes and wrap in double quotes for Windows
    return '"' . str_replace('"', '\\"', $str) . '"';
}

// Test 1: Initial message
echo "Test 1: Initial wedding message\n";
echo "=" . str_repeat("=", 50) . "\n";
$message = "I want to plan a wedding for 150 guests";
$escapedMessage = escapeForWindows($message);
$command = "\"$pythonPath\" \"$pythonScript\" $escapedMessage 2>&1";
echo "Command: $command\n\n";
$output = shell_exec($command);
echo "Output:\n$output\n\n";

$result = json_decode($output, true);
if ($result && isset($result['conversation_state'])) {
    $state = $result['conversation_state'];

    // Test 2: Continue with budget
    echo "Test 2: Adding budget with state\n";
    echo "=" . str_repeat("=", 50) . "\n";
    $message2 = "My budget is 100000 pesos";
    $escapedMessage2 = escapeForWindows($message2);
    $stateJson = json_encode($state);
    $stateArg = escapeForWindows($stateJson);
    $command2 = "\"$pythonPath\" \"$pythonScript\" $escapedMessage2 $stateArg 2>&1";
    echo "Command: $command2\n\n";
    echo "State being passed: $stateJson\n\n";
    $output2 = shell_exec($command2);
    echo "Output:\n$output2\n\n";

    $result2 = json_decode($output2, true);
    if ($result2 && isset($result2['conversation_state'])) {
        $state2 = $result2['conversation_state'];

        // Test 3: Add date
        echo "Test 3: Adding date\n";
        echo "=" . str_repeat("=", 50) . "\n";
        $message3 = "December 2025";
        $escapedMessage3 = escapeForWindows($message3);
        $stateJson3 = json_encode($state2);
        $stateArg3 = escapeForWindows($stateJson3);
        $command3 = "\"$pythonPath\" \"$pythonScript\" $escapedMessage3 $stateArg3 2>&1";
        echo "Command: $command3\n\n";
        echo "State being passed: $stateJson3\n\n";
        $output3 = shell_exec($command3);
        echo "Output:\n$output3\n\n";

        $result3 = json_decode($output3, true);
        if ($result3 && isset($result3['conversation_state'])) {
            $state3 = $result3['conversation_state'];

            // Test 4: Request all services
            echo "Test 4: Request all services\n";
            echo "=" . str_repeat("=", 50) . "\n";
            $message4 = "I need all services";
            $escapedMessage4 = escapeForWindows($message4);
            $stateJson4 = json_encode($state3);
            $stateArg4 = escapeForWindows($stateJson4);
            $command4 = "\"$pythonPath\" \"$pythonScript\" $escapedMessage4 $stateArg4 2>&1";
            echo "Command: $command4\n\n";
            echo "State being passed: $stateJson4\n\n";
            $output4 = shell_exec($command4);
            echo "Output:\n$output4\n\n";
        }
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Tests completed!\n";
