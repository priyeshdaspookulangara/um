document.addEventListener('DOMContentLoaded', function() {
    const taskListContainer = document.getElementById('task-list-container');
    const messageHistory = document.getElementById('message-history');
    const replyFormContainer = document.getElementById('reply-form-container');
    const replyForm = document.getElementById('reply-form');
    const currentTaskIdInput = document.getElementById('current-task-id');
    const messageInput = document.getElementById('message-input');

    let activeTaskId = null;
    let taskPollingInterval;
    let messagePollingInterval;

    const API_URLS = {
        getTasks: 'api/get_tasks.php',
        getConversation: 'api/get_conversation_details.php',
        sendReply: 'api/send_reply.php'
    };

    /**
     * Fetches tasks from the API and renders them in the conversation list.
     */
    async function fetchAndRenderTasks() {
        try {
            const response = await fetch(API_URLS.getTasks);
            if (!response.ok) throw new Error('Failed to fetch tasks.');

            const tasks = await response.json();

            taskListContainer.innerHTML = ''; // Clear existing list
            if (tasks.length === 0) {
                taskListContainer.innerHTML = '<p class="text-muted p-3">No conversations assigned.</p>';
                return;
            }

            tasks.forEach(task => {
                const taskElement = document.createElement('a');
                taskElement.href = '#';
                taskElement.className = 'list-group-item list-group-item-action';
                taskElement.dataset.taskId = task.id;
                taskElement.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${task.task_type}</h6>
                        <small class="text-muted">${task.conversation_status}</small>
                    </div>
                    <p class="mb-1 small">${task.description}</p>
                `;
                taskListContainer.appendChild(taskElement);
            });

        } catch (error) {
            console.error('Error fetching tasks:', error);
            taskListContainer.innerHTML = '<p class="text-danger p-3">Error loading conversations.</p>';
        }
    }

    /**
     * Fetches and displays messages for a given task ID.
     * @param {number} taskId The ID of the task to fetch messages for.
     */
    async function fetchAndRenderMessages(taskId) {
        activeTaskId = taskId;

        // Highlight the selected task
        document.querySelectorAll('#task-list-container .list-group-item-action').forEach(el => {
            el.classList.toggle('active', el.dataset.taskId == taskId);
        });

        messageHistory.innerHTML = '<div class="text-center text-muted mt-5">Loading messages...</div>';

        try {
            const response = await fetch(`${API_URLS.getConversation}?task_id=${taskId}`);
            if (!response.ok) throw new Error('Failed to fetch messages.');

            const messages = await response.json();

            const messagesContainer = document.createElement('div');
            messagesContainer.className = 'messages-container';

            if (messages.length === 0) {
                messagesContainer.innerHTML = '<p class="text-muted text-center">No messages in this conversation yet.</p>';
            } else {
                messages.forEach(msg => {
                    const messageEl = document.createElement('div');
                    messageEl.className = `message ${msg.sender_type}`;
                    messageEl.textContent = msg.message;
                    messagesContainer.appendChild(messageEl);
                });
            }

            messageHistory.innerHTML = '';
            messageHistory.appendChild(messagesContainer);
            messageHistory.scrollTop = messageHistory.scrollHeight; // Scroll to bottom

            // Show and prepare the reply form
            replyFormContainer.style.display = 'block';
            currentTaskIdInput.value = taskId;

        } catch (error) {
            console.error(`Error fetching messages for task ${taskId}:`, error);
            messageHistory.innerHTML = '<p class="text-danger text-center">Error loading messages.</p>';
        }
    }

    /**
     * Handles the submission of the reply form.
     * @param {Event} event The form submission event.
     */
    async function handleReplySubmit(event) {
        event.preventDefault();
        const taskId = currentTaskIdInput.value;
        const message = messageInput.value.trim();

        if (!taskId || !message) return;

        try {
            const response = await fetch(API_URLS.sendReply, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: taskId, message: message })
            });

            if (!response.ok) throw new Error('Failed to send reply.');

            const result = await response.json();

            if (result.success) {
                messageInput.value = ''; // Clear input
                await fetchAndRenderMessages(taskId); // Refresh messages
            } else {
                alert('Error sending reply: ' + (result.error || 'Unknown error'));
            }

        } catch (error) {
            console.error('Error sending reply:', error);
            alert('An unexpected error occurred while sending the reply.');
        }
    }

    // --- Event Listeners and Polling ---

    // Handle clicks on the task list
    taskListContainer.addEventListener('click', function(event) {
        const target = event.target.closest('.list-group-item-action');
        if (target) {
            event.preventDefault();
            const taskId = target.dataset.taskId;
            if (taskId) {
                clearInterval(messagePollingInterval);
                fetchAndRenderMessages(taskId);
                // Start polling for new messages in the active chat
                messagePollingInterval = setInterval(() => fetchAndRenderMessages(taskId), 5000);
            }
        }
    });

    // Handle form submission
    replyForm.addEventListener('submit', handleReplySubmit);

    // Initial load and start polling for the task list
    fetchAndRenderTasks();
    taskPollingInterval = setInterval(fetchAndRenderTasks, 15000);

});
