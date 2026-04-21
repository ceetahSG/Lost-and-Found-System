# Lost and Found System

A modern, database-driven web application for reporting and managing lost and found items.

## ✨ Features

- **User Authentication**: Secure registration and login
- **Post Items**: Report lost or found items with images
- **Advanced Search**: Filter by category, type, location, and status
- **Smart Matching**: Automatic suggestions for similar items
- **Direct Messaging**: Communicate with other users
- **User Dashboard**: Manage your posted items
- **Admin Panel**: Moderate users and items
- **Security**: CSRF protection, password hashing, XSS prevention

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, Tailwind CSS, JavaScript (ES6+)
- **Backend**: PHP 8+
- **Database**: MySQL 8+
- **Server**: Apache

## 📦 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- Composer (optional, for dependency management)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/ceetahSG/Lost-and-Found-System.git
   cd Lost-and-Found-System
   ```

2. **Set up the database**
   - Open phpMyAdmin or MySQL command line
   - Import `database.sql` file
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configure the application**
   - Edit `config/database.php`
   - Update database credentials:
     ```php
     define('DB_USER', 'root');
     define('DB_PASS', 'your_password');
     ```

4. **Set up file permissions**
   ```bash
   chmod -R 755 public/uploads/
   chmod -R 755 public/css/
   chmod -R 755 public/js/
   ```

5. **Start your local server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   ```

6. **Access the application**
   - Open `http://localhost/Lost-and-Found-System/pages/index.php` in your browser

## 📁 Project Structure

```
Lost-and-Found-System/
├── config/
│   └── database.php          # Database configuration
├── classes/
│   ├── User.php              # User management
│   ├── Item.php              # Item operations
│   ├── Message.php           # Messaging system
│   └── Admin.php             # Admin functions
├── includes/
│   ├── functions.php         # Helper functions
│   ├── header.php            # HTML header
│   ├── navbar.php            # Navigation bar
│   └── footer.php            # Footer
├── pages/
│   ├── index.php             # Home page
│   ├── register.php          # Registration
│   ├── login.php             # Login
│   ├── post-item.php         # Post items
│   ├── search.php            # Search items
│   ├── item-detail.php       # Item details
│   ├── messages.php          # Messaging
│   ├── dashboard.php         # User dashboard
│   ├── profile.php           # User profile
│   ├── admin-dashboard.php   # Admin panel
│   ├── edit-item.php         # Edit items
│   └── logout.php            # Logout
├── public/
│   ├── css/
│   │   └── styles.css        # Custom CSS
│   ├── js/
│   │   ├── main.js           # Main JavaScript
│   │   └── validation.js     # Form validation
│   └── uploads/              # User uploads
├── api/
│   └── delete-item.php       # Delete item handler
├── database.sql              # Database schema
├── .htaccess                 # Apache configuration
└── README.md                 # This file
```

## 🔐 Security Features

- **CSRF Protection**: Token-based CSRF prevention
- **Password Security**: Bcrypt hashing with salt
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: HTML entity encoding on output
- **Input Validation**: Server-side validation
- **Session Management**: Secure session handling
- **File Upload Security**: Type and size validation

## 👥 User Roles

### Regular User
- Post lost/found items
- Search and browse items
- Message other users
- View own dashboard
- Edit personal profile

### Admin
- View all users and items
- Ban users for violations
- Delete inappropriate items
- View system statistics
- Manage moderation logs

## 📝 Usage Guide

### Posting an Item
1. Log in to your account
2. Click "Post Item"
3. Fill in the required details
4. Upload an image (optional)
5. Click "Post Item"

### Searching for Items
1. Go to "Search" page
2. Use filters to narrow down results
3. View item details and contact owners
4. Start a conversation

### Managing Your Items
1. Go to your Dashboard
2. View all your posted items
3. Edit or delete items as needed
4. Check messages for responses

## 🐛 Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify credentials in `config/database.php`
- Ensure database is created

### File Upload Issues
- Check `public/uploads/` folder permissions (755)
- Verify file size is under 5MB
- Check allowed file types (JPG, PNG, GIF)

### Session Issues
- Clear browser cookies
- Check PHP session folder permissions
- Verify `session_start()` is called

## 📊 Database Schema

### Users Table
- User authentication and profile information
- Roles: user, admin
- Ban status tracking

### Items Table
- Lost/found item details
- Categories: Electronics, Documents, Accessories, etc.
- Status: active, claimed, resolved

### Messages Table
- Direct messaging between users
- Read status tracking
- Related to items for context

### Matches Table
- Smart matching between lost and found items
- Match score calculation

### Admin Logs Table
- Tracking of administrative actions
- Audit trail for moderation

## 🤝 Contributing

Feel free to fork this project and submit pull requests!

## 📄 License

This project is open source and available under the MIT License.

## 🙏 Acknowledgments

Built with:
- PHP
- MySQL
- Tailwind CSS
- Font Awesome Icons

## 📧 Contact

For questions or support, contact: support@lostandfound.com

---

**Happy coding! 🚀**