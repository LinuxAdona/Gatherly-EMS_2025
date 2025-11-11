// Dark Mode Toggle
const toggleBtn = document.getElementById('toggleMode');
const body = document.body;
const icon = toggleBtn.querySelector('i');

toggleBtn.addEventListener('click', () => {
  body.classList.toggle('dark');
  icon.classList.toggle('fa-sun');
  icon.classList.toggle('fa-moon');
});

// Chat Management
const conversations = document.querySelectorAll('.conversation');
const chatHeader = document.querySelector('.chat-header h4');
const chatStatus = document.querySelector('.chat-header span');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.querySelector('.chat-input input[type="text"]');
const fileInput = document.getElementById('fileInput');
const attachBtn = document.getElementById('attachFile');
const sendBtn = document.querySelector('.chat-input button');

let activeChat = 'ballroom';
const eventId = 1;
const senderId = 2;
const receiverIdMap = {
  ballroom: 3,
  garden: 4,
  skyline: 5
};

const localMessages = {
  ballroom: Array.from(chatMessages.children).map(msgDiv => ({
    sender_id: msgDiv.classList.contains('sent') ? senderId : receiverIdMap['ballroom'],
    message_text: msgDiv.querySelector('p') ? msgDiv.querySelector('p').textContent : '',
    timestamp: msgDiv.querySelector('.timestamp').textContent
  })),
  garden: [{
    sender_id: receiverIdMap['garden'],
    message_text: "The catering package includes vegetarian options.",
    timestamp: "10:05 AM"
  }],
  skyline: [{
    sender_id: receiverIdMap['skyline'],
    message_text: "I can send you the contract today.",
    timestamp: "10:15 AM"
  }]
};

// Display messages properly (no empty bubble if no message)
function displayMessages(chatKey) {
  chatMessages.innerHTML = '';
  localMessages[chatKey].forEach(msg => {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', msg.sender_id == senderId ? 'sent' : 'received');

    let contentHTML = '';

    if (msg.file_url) {
      // IMAGE
      if (msg.file_url.startsWith('data:image') || msg.file_url.match(/\.(jpg|jpeg|png|gif)$/i)) {
        contentHTML += `<img src="${msg.file_url}" class="chat-image" />`;
      }
      // VIDEO
      else if (msg.file_url.startsWith('data:video') || msg.file_url.match(/\.(mp4|mov|webm)$/i)) {
        contentHTML += `
          <video class="chat-image" controls>
            <source src="${msg.file_url}" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        `;
      }
      // OTHER FILES
      else {
        const fileName = msg.file_url.split('/').pop();
        contentHTML += `<a href="${msg.file_url}" download>📎 ${fileName}</a>`;
      }
    }

    // Add message bubble only if text exists
    if (msg.message_text && msg.message_text.trim() !== '') {
      contentHTML += `<p>${msg.message_text}</p>`;
    }

    // Timestamp always last
    contentHTML += `<div class="timestamp">${msg.timestamp}</div>`;

    messageDiv.innerHTML = contentHTML;
    chatMessages.appendChild(messageDiv);
  });
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Load messages from DB
function loadMessages(chatKey = activeChat) {
  const receiverId = receiverIdMap[chatKey];

  fetch('load_messages.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
      event_id: eventId,
      sender_id: senderId,
      receiver_id: receiverId
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.error) return console.error(data.error);

      const existingTexts = localMessages[chatKey].map(m => m.message_text);
      let newCount = 0;

      data.forEach(msg => {
        if (!existingTexts.includes(msg.message_text)) {
          localMessages[chatKey].push(msg);
          if (chatKey !== activeChat && msg.sender_id !== senderId) newCount++;
        }
      });

      const convDiv = document.querySelector(`.conversation[data-chat="${chatKey}"]`);
      let badge = convDiv.querySelector('.badge');
      if (newCount > 0) {
        if (!badge) {
          badge = document.createElement('span');
          badge.classList.add('badge');
          convDiv.appendChild(badge);
        }
        badge.textContent = newCount;
      } else if (badge) {
        badge.remove();
      }

      if (chatKey === activeChat) displayMessages(activeChat);
    })
    .catch(err => console.error(err));
}

// File attachment handling
let attachedFile = null;

attachBtn.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', () => {
  const file = fileInput.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = e => {
    attachedFile = e.target.result; // base64
    chatInput.placeholder = "File attached. Type message or click send...";
  };
  reader.readAsDataURL(file);
});

// Send message (text, file, or both)
function sendMessage() {
  const text = chatInput.value.trim();
  if (!text && !attachedFile) return;

  const timestamp = new Date().toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
  const receiverId = receiverIdMap[activeChat];

  const newMessage = {
    sender_id: senderId,
    message_text: text || '',
    timestamp,
    file_url: attachedFile || null
  };

  // Add to local
  localMessages[activeChat].push(newMessage);
  displayMessages(activeChat);

  // Prepare backend data
  const bodyData = new URLSearchParams({
    event_id: eventId,
    sender_id: senderId,
    receiver_id: receiverId,
    message_text: text
  });
  if (attachedFile) bodyData.append('file_url', attachedFile);

  fetch('save_message.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: bodyData
  })
    .then(res => res.json())
    .then(data => {
      if (!data.success) console.error(data.error || 'Message not sent');
    })
    .catch(err => console.error(err));

  // Reset inputs
  chatInput.value = '';
  chatInput.placeholder = 'Type your message...';
  attachedFile = null;
  fileInput.value = '';
}

// Send events
sendBtn.addEventListener('click', sendMessage);
chatInput.addEventListener('keypress', e => {
  if (e.key === 'Enter') {
    e.preventDefault();
    sendMessage();
  }
});

// Switch conversations
conversations.forEach(conv => {
  conv.addEventListener('click', () => {
    conversations.forEach(c => c.classList.remove('active'));
    conv.classList.add('active');
    activeChat = conv.getAttribute('data-chat');
    const badge = conv.querySelector('.badge');
    if (badge) badge.remove();

    if (activeChat === 'ballroom') {
      chatHeader.innerHTML = "Grand Ballroom Manager";
      chatStatus.textContent = "Active now";
    } else if (activeChat === 'garden') {
      chatHeader.innerHTML = "Garden Paradise Manager";
      chatStatus.textContent = "Active now";
    } else if (activeChat === 'skyline') {
      chatHeader.innerHTML = "Skyline Rooftop Manager";
      chatStatus.textContent = "Online";
    }

    displayMessages(activeChat);
    loadMessages(activeChat);
  });
});

// Initial load + auto refresh
loadMessages();
setInterval(() => loadMessages(activeChat), 3000);
