<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Messages</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f9f9fb;
      height: 100vh;
      display: flex;
      flex-direction: column;
      transition: background-color 0.3s, color 0.3s;
    }

    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fff;
      padding: 12px 40px;
      border-bottom: 1px solid #ddd;
      height: 60px;
      transition: background 0.3s;
    }

    .navbar-left {
      display: flex;
      align-items: center;
      gap: 40px;
    }

    .logo-icon {
      font-weight: bold;
      font-size: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
      color: #0059ff;
    }

    .logo-icon i {
      font-size: 20px;
      color: #0059ff;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .nav-links a {
      text-decoration: none;
      color: #333;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      border-radius: 4px;
      transition: 0.2s;
    }

    .nav-links a:hover {
      background-color: #f2f4f7;
    }

    .nav-links a.active {
      background-color: #0059ff;
      color: white;
    }

    .navbar-right {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .toggle-mode {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 20px;
      color: #555;
      transition: color 0.3s;
    }

    .toggle-mode:hover {
      color: #0059ff;
    }

    .navbar-right button.signin {
      background-color: #0059ff;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 7px 15px;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .container {
      flex-grow: 1;
      padding: 40px 60px;
      display: flex;
      flex-direction: column;
    }

    h2 {
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 25px;
    }

    .messages-container {
      display: flex;
      gap: 20px;
      height: calc(100vh - 160px);
    }

    .sidebar {
      background: white;
      width: 320px;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      transition: background 0.3s, color 0.3s;
    }

    .sidebar h3 {
      font-size: 14px;
      color: #555;
      margin-bottom: 20px;
    }

    .conversation {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.2s;
    }

    .conversation:hover {
      background-color: #f2f4f7;
    }

    .conversation.active {
      background-color: #f2f4f7;
    }

    .conv-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .circle {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background-color: #e5e5e5;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: bold;
      color: #555;
    }

    .conv-text p {
      font-size: 13px;
      color: #333;
      line-height: 1.2;
      font-weight: bold;
    }

    .conv-text span {
      font-size: 12px;
      color: gray;
    }

    .badge {
      background-color: #0059ff;
      color: white;
      font-size: 11px;
      border-radius: 50%;
      padding: 3px 6px;
    }

    .chat-area {
      background: white;
      flex: 1;
      border: 1px solid #ddd;
      border-radius: 8px;
      display: flex;
      flex-direction: column;
      height: 100%;
      transition: background 0.3s, color 0.3s;
    }

    .chat-header {
      padding: 15px 20px;
      border-bottom: 1px solid #ddd;
    }

    .chat-header h4 {
      font-size: 15px;
      font-weight: bold;
      margin-bottom: 3px;
    }

    .chat-header span {
      font-size: 13px;
      color: gray;
    }

    .chat-messages {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      gap: 15px;
      overflow-y: auto;
    }

    .message {
      display: flex;
      flex-direction: column;
      max-width: 75%;
    }

    .received {
      align-self: flex-start;
    }

    .sent {
      align-self: flex-end;
      text-align: right;
    }

    .message p {
      background: #f2f4f7;
      padding: 10px 15px;
      border-radius: 10px;
      font-size: 14px;
      color: #333;
    }

    .sent p {
      background: #0059ff;
      color: white;
    }

    .timestamp {
      font-size: 11px;
      color: gray;
      margin-top: 3px;
    }

    .chat-input {
      border-top: 1px solid #ddd;
      display: flex;
      align-items: center;
      padding: 10px 15px;
      gap: 10px;
    }

    .chat-input i {
      font-size: 18px;
      color: #555;
      cursor: pointer;
      transition: 0.2s;
    }

    .chat-input i:hover {
      color: #0059ff;
    }

    .chat-input input {
      width: 100%;
      border: none;
      outline: none;
      padding: 10px;
      font-size: 14px;
      border-radius: 6px;
      background-color: #f2f4f7;
    }

    .chat-input button {
      background: #0059ff; /* Blue background */
      color: white;         /* White icon */
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center; 
      transition: 0.2s;
}

    .chat-input button i {
      color: white; /* White icon */
}

    body.dark {
      background-color: #1e1e1e;
      color: white;
    }

    body.dark .navbar {
      background: #2b2b2b;
      border-color: #444;
    }

    body.dark .sidebar,
    body.dark .chat-area {
      background: #2b2b2b;
      border-color: #444;
    }

    body.dark .conversation:hover,
    body.dark .conversation.active {
      background: #3a3a3a;
    }

    body.dark .nav-links a {
      color: #ddd;
    }

    body.dark .message p {
      background: #3a3a3a;
      color: white;
    }

    body.dark .sent p {
      background: #0059ff;
      color: white;
    }

    body.dark .chat-input input {
      background: #3a3a3a;
      color: white;
    }

    body.dark .toggle-mode {
      color: #ddd;
    }
  </style>
</head>

<body>
  <div class="navbar">
    <div class="navbar-left">
      <div class="logo-icon">
        <i class="fa-solid fa-gem"></i>
        Gatherly
      </div>

      <div class="nav-links">
        <a href="#"><i class="fa-solid fa-house"></i> Home</a>
        <a href="#"><i class="fa-solid fa-building-columns"></i> Venues</a>
        <a href="#"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        <a href="#" class="active"><i class="fa-solid fa-message"></i> Messages</a>
      </div>
    </div>

    <div class="navbar-right">
      <button class="toggle-mode" id="toggleMode"><i class="fa-solid fa-sun"></i></button>
      <button class="signin"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
    </div>
  </div>

  <div class="container">
    <h2>Messages</h2>
    <div class="messages-container">
      <div class="sidebar">
        <h3>Conversations</h3>

        <div class="conversation active">
          <div class="conv-left">
            <div class="circle">SA</div>
            <div class="conv-text">
              <p>Grand Ballroom</p>
              <span>Yes, we have several dates available in March.</span>
            </div>
          </div>
          <span class="badge">2</span>
        </div>

        <div class="conversation">
          <div class="conv-left">
            <div class="circle">MI</div>
            <div class="conv-text">
              <p>Garden Paradise</p>
              <span>The catering package includes...</span>
            </div>
          </div>
        </div>

        <div class="conversation">
          <div class="conv-left">
            <div class="circle">EM</div>
            <div class="conv-text">
              <p>Skyline Rooftop</p>
              <span>I can send you the contract today.</span>
            </div>
          </div>
          <span class="badge">1</span>
        </div>
      </div>

      <div class="chat-area">
        <div class="chat-header">
          <h4>Grand Ballroom Manager</h4>
          <span>Usually replies within an hour</span>
        </div>

        <div class="chat-messages">
          <div class="message received">
            <p>Hello! Thank you for your interest in our venue. I’d be happy to answer any questions you have.</p>
            <div class="timestamp">09:49 AM</div>
          </div>

          <div class="message sent">
            <p>Hi! I'm interested in booking for a corporate event in March. Do you have availability?</p>
            <div class="timestamp">09:59 AM</div>
          </div>
        </div>

        <div class="chat-input">
          <i class="fa-solid fa-paperclip"></i>
          <input type="text" placeholder="Type your message..." />
          <button><i class="fa-solid fa-paper-plane"></i></button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const toggleBtn = document.getElementById('toggleMode');
    const body = document.body;
    const icon = toggleBtn.querySelector('i');

    toggleBtn.addEventListener('click', () => {
      body.classList.toggle('dark');
      icon.classList.toggle('fa-sun');
      icon.classList.toggle('fa-moon');
    });
  </script>
</body>
</html>
