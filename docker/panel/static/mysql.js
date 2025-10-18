// MySQL Management JavaScript Functions

// Global variables
let currentDatabase = '';
let currentTable = '';
let databases = [];
let users = [];

// Tab switching function
function switchTab(tabName) {
  // Hide all tab panels
  document.querySelectorAll('.tab-panel').forEach(panel => {
    panel.classList.add('hidden');
  });
  
  // Remove active class from all tab buttons
  document.querySelectorAll('.tab-button').forEach(button => {
    button.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
    button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
  });
  
  // Show selected tab panel
  document.getElementById(tabName + '-tab').classList.remove('hidden');
  
  // Add active class to selected tab button
  const activeTab = document.getElementById('tab-' + tabName);
  activeTab.classList.add('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
  activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
  
  // Load data for the tab if needed
  switch(tabName) {
    case 'databases':
      loadDatabases();
      break;
    case 'tables':
      loadTables();
      break;
    case 'users':
      loadUsers();
      break;
    case 'replication':
      loadReplicationStatus();
      break;
    case 'logs':
      loadErrorLogs();
      break;
  }
}

// Modal functions
function openCreateDatabaseModal() {
  document.getElementById('createDatabaseModal').classList.remove('hidden');
  document.getElementById('createDatabaseModal').classList.add('flex');
  document.getElementById('newDatabaseName').focus();
}

function closeCreateDatabaseModal() {
  document.getElementById('createDatabaseModal').classList.add('hidden');
  document.getElementById('createDatabaseModal').classList.remove('flex');
  document.getElementById('newDatabaseName').value = '';
}

function openCreateUserModal() {
  document.getElementById('createUserModal').classList.remove('hidden');
  document.getElementById('createUserModal').classList.add('flex');
  document.getElementById('newUsername').focus();
}

function closeCreateUserModal() {
  document.getElementById('createUserModal').classList.add('hidden');
  document.getElementById('createUserModal').classList.remove('flex');
  document.getElementById('newUsername').value = '';
  document.getElementById('newUserPassword').value = '';
  document.getElementById('newUserHost').value = '%';
  document.getElementById('newUserPrivileges').value = 'ALL PRIVILEGES';
}

function loadCurrentRootPassword() {
  const currentLabel = document.getElementById('currentRootPassword');
  if (!currentLabel) return;
  currentLabel.textContent = '(loading...)';
  fetch('/api/mysql/get-root-password')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try { data = text ? JSON.parse(text) : null; } catch { throw new Error('Invalid JSON response'); }
      if (!response.ok) throw new Error(data?.error || 'Failed to get root password');
      return data;
    })
    .then(data => {
      const pwd = data?.password || '';
      if (pwd) {
        // Obfuscate display but still set input value
        const obfuscated = '*'.repeat(Math.min(pwd.length, 12));
        currentLabel.textContent = obfuscated;
        const input = document.getElementById('rootPassword');
        if (input) input.value = pwd;
      } else {
        currentLabel.textContent = '(empty)';
      }
    })
    .catch(err => {
      console.error('Error getting root password:', err);
      currentLabel.textContent = '(error)';
    });
}

function openSetRootPasswordModal() {
  document.getElementById('setRootPasswordModal').classList.remove('hidden');
  document.getElementById('setRootPasswordModal').classList.add('flex');
  loadCurrentRootPassword();
  const input = document.getElementById('rootPassword');
  if (input) input.focus();
  const toggleBtn = document.getElementById('toggleRootPasswordBtn');
  if (toggleBtn) {
    toggleBtn.onclick = function() {
      const inp = document.getElementById('rootPassword');
      if (!inp) return;
      const icon = this.querySelector('i');
      const toText = inp.type === 'password';
      inp.type = toText ? 'text' : 'password';
      if (icon) icon.className = toText ? 'fas fa-eye-slash' : 'fas fa-eye';
    };
  }
}

function closeSetRootPasswordModal() {
  document.getElementById('setRootPasswordModal').classList.add('hidden');
  document.getElementById('setRootPasswordModal').classList.remove('flex');
  document.getElementById('rootPassword').value = '';
  const currentLabel = document.getElementById('currentRootPassword');
  if (currentLabel) currentLabel.textContent = '';
  const toggleBtn = document.getElementById('toggleRootPasswordBtn');
  if (toggleBtn) {
    const icon = toggleBtn.querySelector('i');
    const inp = document.getElementById('rootPassword');
    if (inp) inp.type = 'password';
    if (icon) icon.className = 'fas fa-eye';
  }
}

function openEditConfigModal() {
  document.getElementById('editConfigModal').classList.remove('hidden');
  document.getElementById('editConfigModal').classList.add('flex');
  loadMySQLConfig();
}

function closeEditConfigModal() {
  document.getElementById('editConfigModal').classList.add('hidden');
  document.getElementById('editConfigModal').classList.remove('flex');
}

// MySQL Info functions
function getMySQLInfo() {
  fetch('/api/mysql-info')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to get MySQL info');
      }
      return response.json();
    })
    .then(data => {
      const content = document.getElementById('mysqlInfoContent');
      const statusBadge = document.getElementById('mysqlStatusBadge');

      const version = data?.version?.version ?? data?.version ?? 'N/A';
      const uptime = data?.status?.Uptime ?? 'N/A';
      const connections = data?.status?.Threads_connected ?? 'N/A';
      const queries = data?.status?.Questions ?? 'N/A';

      content.innerHTML = `
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><strong>Version:</strong> ${version}</div>
          <div><strong>Uptime:</strong> ${uptime}</div>
          <div><strong>Connections:</strong> ${connections}</div>
          <div><strong>Queries:</strong> ${queries}</div>
        </div>
      `;

      statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Connected';
      statusBadge.className = 'px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-sm';
    })
    .catch(error => {
      console.error('Error getting MySQL info:', error);
      const content = document.getElementById('mysqlInfoContent');
      const statusBadge = document.getElementById('mysqlStatusBadge');

      content.innerHTML = `
        <div class="text-red-600 dark:text-red-400">
          <i class="fas fa-exclamation-circle mr-2"></i>
          Error: ${error.message}
        </div>
      `;

      statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Error';
      statusBadge.className = 'px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full text-sm';
    });
}

// Database functions
function loadDatabases() {
  fetch('/api/mysql/databases')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load databases');
      }
      return response.json();
    })
    .then(data => {
      if (!Array.isArray(data)) {
        throw new Error('Invalid databases response');
      }
      databases = data;
      displayDatabases(databases);
      updateDatabaseSelectors();
    })
    .catch(error => {
      console.error('Error loading databases:', error);
      document.getElementById('databasesTableBody').innerHTML = `
        <tr>
          <td colspan="4" class="px-6 py-8 text-center text-red-600 dark:text-red-400">
            <i class="fas fa-exclamation-circle mr-2"></i>
            Error: ${error.message}
          </td>
        </tr>
      `;
    });
}

function displayDatabases(databases) {
  const tbody = document.getElementById('databasesTableBody');
  
  if (databases.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
          <i class="fas fa-database text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
          <p class="text-lg">No databases found</p>
          <p class="text-sm">Create your first database to get started</p>
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = databases.map(db => `
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
          <i class="fas fa-database text-blue-600 dark:text-blue-400 mr-3"></i>
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">${db.name}</span>
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        ${db.tables || 0}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        ${formatBytes(db.size || 0)}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2">
          <button onclick="viewTables('${db.name}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
            <i class="fas fa-eye mr-1"></i>View Tables
          </button>
          <button onclick="deleteDatabase('${db.name}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
            <i class="fas fa-trash mr-1"></i>Delete
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

function createDatabase() {
  const dbName = document.getElementById('newDatabaseName').value.trim();
  
  if (!dbName) {
    alert('Please enter a database name');
    return;
  }
  
  if (!/^[a-zA-Z0-9_]+$/.test(dbName)) {
    alert('Database name can only contain letters, numbers, and underscores');
    return;
  }
  
  fetch('/api/mysql/create-db', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ name: dbName })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to create database';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Database created successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Database created successfully', 'success');
      closeCreateDatabaseModal();
      loadDatabases();
    } else {
      throw new Error(data?.error || 'Failed to create database');
    }
  })
  .catch(error => {
    console.error('Error creating database:', error);
    alert('Error creating database: ' + error.message);
  });
}

function deleteDatabase(dbName) {
  if (!confirm(`Are you sure you want to delete the database "${dbName}"? This action cannot be undone.`)) {
    return;
  }
  
  fetch('/api/mysql/delete-db', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ name: dbName })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to delete database';
      throw new Error(errMsg);
    }
    return data;
  })
  .then((data) => {
    const msg = data?.message || data?.success || 'Database deleted successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Database deleted successfully', 'success');
      loadDatabases();
    } else {
      throw new Error(data?.error || 'Failed to delete database');
    }
  })
  .catch(error => {
    console.error('Error deleting database:', error);
    alert('Error deleting database: ' + error.message);
  });
}

function viewTables(dbName) {
  currentDatabase = dbName;
  document.getElementById('databaseSelector').value = dbName;
  switchTab('tables');
}

// Table functions
function loadTables() {
  const dbName = document.getElementById('databaseSelector').value;
  
  if (!dbName) {
    document.getElementById('tablesTableBody').innerHTML = `
      <tr>
        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
          Please select a database first
        </td>
      </tr>
    `;
    return;
  }
  
  fetch(`/api/mysql/tables?database=${encodeURIComponent(dbName)}`)
    .then(async (response) => {
      if (!response.ok) {
        // Could be redirected to login or server error
        const text = await response.text().catch(() => '');
        throw new Error('Failed to load tables');
      }
      // Parse JSON safely
      let data;
      try {
        data = await response.json();
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      // Backend returns array directly; fallback to data.tables if present
      const tables = Array.isArray(data) ? data : (Array.isArray(data?.tables) ? data.tables : null);
      if (!tables) {
        const msg = (data && typeof data === 'object' && data.error) ? data.error : 'Invalid tables response';
        throw new Error(msg);
      }
      displayTables(tables);
    })
    .catch(error => {
      console.error('Error loading tables:', error);
      document.getElementById('tablesTableBody').innerHTML = `
        <tr>
          <td colspan="4" class="px-6 py-8 text-center text-red-600 dark:text-red-400">
            <i class="fas fa-exclamation-circle mr-2"></i>
            Error: ${error.message}
          </td>
        </tr>
      `;
    });
}

function displayTables(tables) {
  const tbody = document.getElementById('tablesTableBody');
  
  if (tables.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
          <i class="fas fa-table text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
          <p class="text-lg">No tables found</p>
          <p class="text-sm">Create tables in this database to see them here</p>
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = tables.map(table => `
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
          <i class="fas fa-table text-blue-600 dark:text-blue-400 mr-3"></i>
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">${table.name}</span>
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        ${table.rows || 0}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        ${formatBytes(table.size || 0)}
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2">
          <button onclick="viewTableData('${table.name}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
            <i class="fas fa-eye mr-1"></i>View Data
          </button>
          <button onclick="deleteTable('${table.name}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
            <i class="fas fa-trash mr-1"></i>Delete
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

function deleteTable(tableName) {
  const dbName = document.getElementById('databaseSelector').value;
  
  if (!confirm(`Are you sure you want to delete the table "${tableName}" from database "${dbName}"? This action cannot be undone.`)) {
    return;
  }
  
  fetch('/api/mysql/delete-table', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ 
      database: dbName,
      table: tableName 
    })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to delete table';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Table deleted successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Table deleted successfully', 'success');
      loadTables();
    } else {
      throw new Error(data?.error || 'Failed to delete table');
    }
  })
  .catch(error => {
    console.error('Error deleting table:', error);
    alert('Error deleting table: ' + error.message);
  });
}

function viewTableData(tableName) {
  currentTable = tableName;
  // Switch to queries tab and load table data
  switchTab('queries');
  document.getElementById('sqlQuery').value = `SELECT * FROM \`${tableName}\` LIMIT 100;`;
}

// User functions
function loadUsers() {
  fetch('/api/mysql/users')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load users');
      }
      return response.json();
    })
    .then(data => {
      const list = Array.isArray(data) ? data : (data && Array.isArray(data.users) ? data.users : null);
      if (!list) {
        throw new Error('Invalid users response');
      }
      // Normalize keys to match UI expectations: user, host, privileges
      users = list.map(u => ({
        user: u.user ?? u.User ?? '',
        host: u.host ?? u.Host ?? '%',
        privileges: u.privileges ?? 'No privileges'
      }));
      displayUsers(users);
    })
    .catch(error => {
      console.error('Error loading users:', error);
      document.getElementById('usersTableBody').innerHTML = `
        <tr>
          <td colspan="4" class="px-6 py-8 text-center text-red-600 dark:text-red-400">
            <i class="fas fa-exclamation-circle mr-2"></i>
            Error: ${error.message}
          </td>
        </tr>
      `;
    });
}

function displayUsers(users) {
  const tbody = document.getElementById('usersTableBody');
  
  if (users.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
          <i class="fas fa-users text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
          <p class="text-lg">No users found</p>
          <p class="text-sm">Create MySQL users to see them here</p>
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = users.map(user => `
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
          <i class="fas fa-user text-blue-600 dark:text-blue-400 mr-3"></i>
          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">${user.user}</span>
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        ${user.host}
      </td>
      <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
        <div class="max-w-xs truncate">
          ${user.privileges || 'No privileges'}
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2">
          <button onclick="editUser('${user.user}', '${user.host}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
            <i class="fas fa-edit mr-1"></i>Edit
          </button>
          <button onclick="deleteUser('${user.user}', '${user.host}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
            <i class="fas fa-trash mr-1"></i>Delete
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

function createUser() {
  const username = document.getElementById('newUsername').value.trim();
  const password = document.getElementById('newUserPassword').value;
  const host = document.getElementById('newUserHost').value.trim();
  const privileges = document.getElementById('newUserPrivileges').value;
  
  if (!username) {
    alert('Please enter a username');
    return;
  }
  
  if (!password) {
    alert('Please enter a password');
    return;
  }
  
  if (!/^[a-zA-Z0-9_]+$/.test(username)) {
    alert('Username can only contain letters, numbers, and underscores');
    return;
  }
  
  fetch('/api/mysql/create-user', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      username: username,
      password: password,
      host: host,
      privileges: privileges
    })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to create user';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'User created successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'User created successfully', 'success');
      closeCreateUserModal();
      loadUsers();
    } else {
      throw new Error(data?.error || 'Failed to create user');
    }
  })
  .catch(error => {
    console.error('Error creating user:', error);
    alert('Error creating user: ' + error.message);
  });
}

function deleteUser(username, host) {
  if (username === 'root') {
    alert('Cannot delete root user');
    return;
  }
  
  if (!confirm(`Are you sure you want to delete the user "${username}"@"${host}"? This action cannot be undone.`)) {
    return;
  }
  
  fetch('/api/mysql/delete-user', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      username: username,
      host: host
    })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to delete user';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'User deleted successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'User deleted successfully', 'success');
      loadUsers();
    } else {
      throw new Error(data?.error || 'Failed to delete user');
    }
  })
  .catch(error => {
    console.error('Error deleting user:', error);
    alert('Error deleting user: ' + error.message);
  });
}

function editUser(username, host) {
  const newPassword = prompt(`Enter new password for user "${username}"@"${host}" (leave empty to keep current password):`);
  
  if (newPassword === null) {
    return; // User cancelled
  }
  
  const privileges = prompt(`Enter new privileges for user "${username}"@"${host}" (leave empty to keep current privileges):`, 'ALL PRIVILEGES');
  
  if (privileges === null) {
    return; // User cancelled
  }
  
  const updateData = {
    username: username,
    host: host
  };
  
  if (newPassword) {
    updateData.password = newPassword;
  }
  
  if (privileges) {
    updateData.privileges = privileges;
  }
  
  fetch('/api/mysql/update-user', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(updateData)
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to update user';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'User updated successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'User updated successfully', 'success');
      loadUsers();
    } else {
      throw new Error(data?.error || 'Failed to update user');
    }
  })
  .catch(error => {
    console.error('Error updating user:', error);
    alert('Error updating user: ' + error.message);
  });
}

// Query functions
function executeQuery() {
  const query = document.getElementById('sqlQuery').value.trim();
  const database = document.getElementById('queryDatabase').value;
  
  if (!query) {
    alert('Please enter a SQL query');
    return;
  }
  
  const resultsDiv = document.getElementById('queryResults');
  const statusSpan = document.getElementById('queryStatus');
  
  resultsDiv.classList.remove('hidden');
  statusSpan.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Executing...';
  statusSpan.className = 'text-xs text-blue-600 dark:text-blue-400';
  
  fetch('/api/mysql/execute-query', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      query: query,
      database: database
    })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Query execution failed';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    if (!Array.isArray(data)) {
      throw new Error('Invalid query response');
    }
    // Display results using existing renderer (expects {results: [...]})
    displayQueryResults({ results: data });
    
    // Determine status message
    if (data.length === 1 && typeof data[0] === 'object' && data[0] && 'affected_rows' in data[0]) {
      const affected = Number(data[0].affected_rows) || 0;
      statusSpan.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${affected} rows affected`;
    } else {
      statusSpan.innerHTML = `<i class="fas fa-check-circle mr-1"></i>${data.length} rows returned`;
    }
    statusSpan.className = 'text-xs text-green-600 dark:text-green-400';
  })
  .catch(error => {
    console.error('Error executing query:', error);
    document.getElementById('queryResultsTable').innerHTML = `
      <tr>
        <td class="px-6 py-4 text-center text-red-600 dark:text-red-400">
          <i class="fas fa-exclamation-circle mr-2"></i>
          Error: ${error.message}
        </td>
      </tr>
    `;
    statusSpan.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>Error`;
    statusSpan.className = 'text-xs text-red-600 dark:text-red-400';
  });
}

function displayQueryResults(data) {
  const tbody = document.getElementById('queryResultsTable');
  
  if (!data.results || data.results.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
          Query executed successfully. No results to display.
        </td>
      </tr>
    `;
    return;
  }
  
  const headers = Object.keys(data.results[0]);
  
  tbody.innerHTML = `
    <thead class="bg-gray-50 dark:bg-gray-700">
      <tr>
        ${headers.map(header => `
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            ${header}
          </th>
        `).join('')}
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      ${data.results.map(row => `
        <tr>
          ${headers.map(header => `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
              ${row[header] !== null && row[header] !== undefined ? row[header] : 'NULL'}
            </td>
          `).join('')}
        </tr>
      `).join('')}
    </tbody>
  `;
}

function clearQuery() {
  document.getElementById('sqlQuery').value = '';
  document.getElementById('queryResults').classList.add('hidden');
}

// Replication functions
function loadReplicationStatus() {
  fetch('/api/mysql/replication/status')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      if (!response.ok) {
        const errMsg = (data && data.error) ? data.error : 'Failed to load replication status';
        throw new Error(errMsg);
      }
      return data;
    })
    .then(data => {
      if (data && (data.success || data.status)) {
        // backend returns {success: True, status: {...}}
        displayReplicationStatus(data.status || data);
      } else {
        throw new Error(data?.error || 'Failed to load replication status');
      }
    })
    .catch(error => {
      console.error('Error loading replication status:', error);
      document.getElementById('masterStatus').innerHTML = `
        <div class="text-red-600 dark:text-red-400">
          <i class="fas fa-exclamation-circle mr-2"></i>
          Error: ${error.message}
        </div>
      `;
      document.getElementById('slaveStatus').innerHTML = `
        <div class="text-red-600 dark:text-red-400">
          <i class="fas fa-exclamation-circle mr-2"></i>
          Error: ${error.message}
        </div>
      `;
    });
}

function displayReplicationStatus(data) {
  const masterStatus = document.getElementById('masterStatus');
  const slaveStatus = document.getElementById('slaveStatus');

  // Normalize master data: support {status: {master}}, {master}, or legacy {master_status}
  const master = (data && (data.master ?? data.master_status)) ?? null;

  if (master) {
    const file = master.file || master.File || 'N/A';
    const position = master.position || master.Position || 'N/A';
    masterStatus.innerHTML = `
      <div class="space-y-2">
        <div class="flex items-center text-green-600 dark:text-green-400">
          <i class="fas fa-check-circle mr-2"></i>
          <span class="font-medium">Master is configured</span>
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-400">
          <div><strong>File:</strong> ${file}</div>
          <div><strong>Position:</strong> ${position}</div>
        </div>
      </div>
    `;
  } else {
    masterStatus.innerHTML = `
      <div class="flex items-center text-yellow-600 dark:text-yellow-400">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <span class="font-medium">Master is not configured</span>
      </div>
    `;
  }

  // Normalize slave data: backend returns array in status.slave; legacy had single object slave_status
  const slaveList = data && (Array.isArray(data.slave) ? data.slave : null);
  const slaveObj = slaveList ? slaveList[0] : (data && data.slave_status ? data.slave_status : null);

  if (slaveObj) {
    const ioRunning = (slaveObj.slave_io_running || slaveObj.Slave_IO_Running || '').toString();
    const sqlRunning = (slaveObj.slave_sql_running || slaveObj.Slave_SQL_Running || '').toString();
    const isRunning = (ioRunning === 'Yes' && sqlRunning === 'Yes');
    const masterHost = slaveObj.master_host || slaveObj.Master_Host || 'N/A';
    const behind = slaveObj.seconds_behind_master ?? slaveObj.Seconds_Behind_Master;

    slaveStatus.innerHTML = `
      <div class="space-y-2">
        <div class="flex items-center ${isRunning ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
          <i class="fas fa-${isRunning ? 'check-circle' : 'times-circle'} mr-2"></i>
          <span class="font-medium">Slave is ${isRunning ? 'running' : 'stopped'}</span>
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-400">
          <div><strong>Master:</strong> ${masterHost}</div>
          <div><strong>Behind:</strong> ${behind != null ? behind : 'N/A'} seconds</div>
        </div>
      </div>
    `;
  } else {
    slaveStatus.innerHTML = `
      <div class="flex items-center text-yellow-600 dark:text-yellow-400">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <span class="font-medium">Slave is not configured</span>
      </div>
    `;
  }
}

function setupMaster() {
  const serverId = prompt('Enter server ID (unique number, e.g., 1):', '1');
  const logBin = prompt('Enter binary log name (e.g., mysql-bin):', 'mysql-bin');
  
  if (!serverId || !logBin) {
    return;
  }
  
  fetch('/api/mysql/replication/setup-master', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      server_id: parseInt(serverId),
      log_bin: logBin
    })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to setup master';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Master setup completed successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Master setup completed successfully', 'success');
      loadReplicationStatus();
    } else {
      throw new Error(data?.error || 'Failed to setup master');
    }
  })
  .catch(error => {
    console.error('Error setting up master:', error);
    alert('Error setting up master: ' + error.message);
  });
}

function disableMaster() {
  if (!confirm('Are you sure you want to disable master replication? This will remove master configuration.')) {
    return;
  }
  
  fetch('/api/mysql/replication/disable-master', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to disable master';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Master disabled successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Master disabled successfully', 'success');
      loadReplicationStatus();
    } else {
      throw new Error(data?.error || 'Failed to disable master');
    }
  })
  .catch(error => {
    console.error('Error disabling master:', error);
    alert('Error disabling master: ' + error.message);
  });
}

function setupSlave() {
  const masterHost = prompt('Enter master host:');
  const masterUser = prompt('Enter master username (replication user):');
  const masterPassword = prompt('Enter master password:');
  const serverId = prompt('Enter slave server ID (unique number, different from master):', '2');
  
  if (!masterHost || !masterUser || !masterPassword || !serverId) {
    return;
  }
  
  fetch('/api/mysql/replication/setup-slave', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      master_host: masterHost,
      master_user: masterUser,
      master_password: masterPassword,
      master_log_file: '',
      master_log_pos: 0,
      server_id: parseInt(serverId)
    })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to setup slave';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Slave setup completed successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Slave setup completed successfully', 'success');
      loadReplicationStatus();
    } else {
      throw new Error(data?.error || 'Failed to setup slave');
    }
  })
  .catch(error => {
    console.error('Error setting up slave:', error);
    alert('Error setting up slave: ' + error.message);
  });
}

function startSlave() {
  fetch('/api/mysql/replication/start-slave', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to start slave';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Slave started successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Slave started successfully', 'success');
      loadReplicationStatus();
    } else {
      throw new Error(data?.error || 'Failed to start slave');
    }
  })
  .catch(error => {
    console.error('Error starting slave:', error);
    alert('Error starting slave: ' + error.message);
  });
}

function stopSlave() {
  fetch('/api/mysql/replication/stop-slave', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to stop slave';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Slave stopped successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Slave stopped successfully', 'success');
      loadReplicationStatus();
    } else {
      throw new Error(data?.error || 'Failed to stop slave');
    }
  })
  .catch(error => {
    console.error('Error stopping slave:', error);
    alert('Error stopping slave: ' + error.message);
  });
}

function resetSlave() {
  if (!confirm('Are you sure you want to reset slave? This will remove all slave configuration.')) {
    return;
  }
  
  fetch('/api/mysql/replication/reset-slave', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    }
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && data.error) ? data.error : 'Failed to reset slave';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Slave reset successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Slave reset successfully', 'success');
      loadReplicationStatus();
    } else {
      throw new Error(data?.error || 'Failed to reset slave');
    }
  })
  .catch(error => {
    console.error('Error resetting slave:', error);
    alert('Error resetting slave: ' + error.message);
  });
}

// Log functions
function loadErrorLogs() {
  fetch('/api/mysql/error-logs')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      if (!response.ok) {
        const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to load error logs';
        throw new Error(errMsg);
      }
      return data;
    })
    .then(data => {
      if (data && (data.success || data.logs)) {
        document.getElementById('errorLogs').textContent = data.logs || 'No error logs available';
      } else {
        throw new Error(data?.error || 'Failed to load error logs');
      }
    })
    .catch(error => {
      console.error('Error loading error logs:', error);
      document.getElementById('errorLogs').textContent = `Error: ${error.message}`;
    });
}

function loadReplicationLogs() {
  fetch('/api/mysql/replication-logs')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      if (!response.ok) {
        const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to load replication logs';
        throw new Error(errMsg);
      }
      return data;
    })
    .then(data => {
      if (data && (data.success || data.logs)) {
        document.getElementById('replicationLogs').textContent = data.logs || 'No replication logs available';
      } else {
        throw new Error(data?.error || 'Failed to load replication logs');
      }
    })
    .catch(error => {
      console.error('Error loading replication logs:', error);
      document.getElementById('replicationLogs').textContent = `Error: ${error.message}`;
    });
}

function loadSlowLogs() {
  fetch('/api/mysql/slow-logs')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      if (!response.ok) {
        const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to load slow logs';
        throw new Error(errMsg);
      }
      return data;
    })
    .then(data => {
      if (data && (data.success || data.logs)) {
        document.getElementById('slowLogs').textContent = data.logs || 'No slow query logs available';
      } else {
        throw new Error(data?.error || 'Failed to load slow logs');
      }
    })
    .catch(error => {
      console.error('Error loading slow logs:', error);
      document.getElementById('slowLogs').textContent = `Error: ${error.message}`;
    });
}

function analyzeSlowLogs() {
  fetch('/api/mysql/analyze-slow-logs')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      if (!response.ok) {
        const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to analyze slow logs';
        throw new Error(errMsg);
      }
      return data;
    })
    .then(data => {
      if (data && (data.success || data.analysis)) {
        document.getElementById('slowLogs').textContent = data.analysis || 'Analysis completed';
        showNotification('Slow query analysis completed', 'success');
      } else {
        throw new Error(data?.error || 'Failed to analyze slow logs');
      }
    })
    .catch(error => {
      console.error('Error analyzing slow logs:', error);
      alert('Error analyzing slow logs: ' + error.message);
    });
}

// Configuration functions
function loadMySQLConfig() {
  fetch('/api/mysql/get-config')
    .then(async (response) => {
      const text = await response.text();
      let data;
      try {
        data = text ? JSON.parse(text) : null;
      } catch (e) {
        throw new Error('Invalid JSON response');
      }
      if (!response.ok) {
        const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to load MySQL configuration';
        throw new Error(errMsg);
      }
      return data;
    })
    .then(data => {
      if (data && (data.success || data.config)) {
        document.getElementById('mysqlConfigContent').value = data.config || '';
      } else {
        throw new Error(data?.error || 'Failed to load MySQL configuration');
      }
    })
    .catch(error => {
      console.error('Error loading MySQL configuration:', error);
      document.getElementById('mysqlConfigContent').value = `# Error loading configuration: ${error.message}`;
    });
}

function saveMySQLConfig() {
  const config = document.getElementById('mysqlConfigContent').value;
  
  if (!confirm('Are you sure you want to save the MySQL configuration? This will restart the MySQL service.')) {
    return;
  }
  
  fetch('/api/mysql/save-config', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ config: config })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to save configuration';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Configuration saved successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Configuration saved successfully', 'success');
      closeEditConfigModal();
      // Refresh MySQL info after config change
      setTimeout(getMySQLInfo, 5000);
    } else {
      throw new Error(data?.error || 'Failed to save configuration');
    }
  })
  .catch(error => {
    console.error('Error saving MySQL configuration:', error);
    alert('Error saving configuration: ' + error.message);
  });
}

function setRootPassword() {
  const password = document.getElementById('rootPassword').value;
  
  if (!password) {
    alert('Please enter a password');
    return;
  }
  
  if (password.length < 8) {
    alert('Password must be at least 8 characters long');
    return;
  }
  
  fetch('/api/mysql/set-root-password', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ password: password })
  })
  .then(async (response) => {
    const text = await response.text();
    let data;
    try {
      data = text ? JSON.parse(text) : null;
    } catch (e) {
      throw new Error('Invalid JSON response');
    }
    if (!response.ok) {
      const errMsg = (data && (data.error || data.message)) ? (data.error || data.message) : 'Failed to set root password';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    const msg = data?.message || data?.success || 'Root password updated successfully';
    if (msg) {
      showNotification(typeof msg === 'string' ? msg : 'Root password updated successfully', 'success');
      closeSetRootPasswordModal();
    } else {
      throw new Error(data?.error || 'Failed to set root password');
    }
  })
  .catch(error => {
    console.error('Error setting root password:', error);
    alert('Error setting root password: ' + error.message);
  });
}

// Utility functions
function updateDatabaseSelectors() {
  const selectors = [
    document.getElementById('databaseSelector'),
    document.getElementById('queryDatabase')
  ];
  
  selectors.forEach(selector => {
    if (selector) {
      selector.innerHTML = '<option value="">Select Database</option>' +
        databases.map(db => `
          <option value="${db.name}">${db.name}</option>
        `).join('');
    }
  });
}

function formatBytes(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 px-6 py-3 rounded shadow-lg z-50 ${
    type === 'success' ? 'bg-green-500 text-white' :
    type === 'error' ? 'bg-red-500 text-white' :
    'bg-blue-500 text-white'
  }`;
  notification.innerHTML = `
    <div class="flex items-center">
      <i class="fas fa-${
        type === 'success' ? 'check-circle' :
        type === 'error' ? 'exclamation-circle' :
        'info-circle'
      } mr-2"></i>
      ${message}
    </div>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 3000);
}

function refreshAllData() {
  showNotification('Refreshing data...', 'info');
  
  // Refresh based on current tab
  const activeTab = document.querySelector('.tab-button.active').id.replace('tab-', '');
  
  switch(activeTab) {
    case 'databases':
      loadDatabases();
      break;
    case 'tables':
      loadTables();
      break;
    case 'users':
      loadUsers();
      break;
    case 'replication':
      loadReplicationStatus();
      break;
    case 'logs':
      loadErrorLogs();
      loadReplicationLogs();
      loadSlowLogs();
      break;
  }
  
  // Always refresh MySQL info
  getMySQLInfo();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
  // Ctrl+Enter to execute query
  if (e.ctrlKey && e.key === 'Enter' && document.getElementById('sqlQuery') === document.activeElement) {
    e.preventDefault();
    executeQuery();
  }
  
  // Escape to close modals
  if (e.key === 'Escape') {
    closeCreateDatabaseModal();
    closeCreateUserModal();
    closeSetRootPasswordModal();
    closeEditConfigModal();
  }
});