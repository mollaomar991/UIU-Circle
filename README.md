# UIU Alumni Association Platform

> A comprehensive web-based platform connecting United International University alumni and students through mentorship, networking, and community engagement.

## 📚 DBMS Lab Project

This project was developed as part of the **Database Management Systems (DBMS) Lab** course, demonstrating practical implementation of database design principles, normalization, and real-world application development.

## 🎯 Project Overview

The UIU Alumni Association Platform is a full-stack web application that bridges the gap between current students and alumni, fostering a vibrant community through various features including mentorship programs, job opportunities, fundraising campaigns, and memory sharing.

## ✨ Key Features

- **👥 User Management** - Role-based authentication (Students, Alumni, Admins)
- **💬 Real-time Messaging** - Private messaging with notification system
- **🎓 Mentorship Program** - Connect students with alumni mentors
- **💼 Job Board** - Alumni post opportunities, students discover careers
- **📅 Events System** - RSVP to reunions, workshops, and networking events
- **💰 Fundraising** - SSLCommerz payment integration for donations
- **📸 Memory Wall** - Alumni-exclusive photo gallery with social features
- **🔔 Notifications** - Real-time updates for all activities

## 🛠️ Technology Stack

**Frontend:**
- HTML5, CSS3, JavaScript
- Bootstrap 5 / MDB UI Kit
- Responsive design

**Backend:**
- PHP 8.x
- MySQL 8.x
- Apache/XAMPP

**Third-Party:**
- SSLCommerz Payment Gateway

## 🗄️ Database Design

**13 Core Tables:**
- `users` - Student and alumni profiles
- `posts` - User-generated content
- `messages` - Private messaging
- `events` & `event_participants` - Event management
- `jobs` - Job postings
- `mentorship_requests` - Mentor-mentee connections
- `fundraisers` & `donations` - Fundraising campaigns
- `gallery` & `gallery_likes` - Photo sharing
- `notifications` - Alert system
- `admin` - Platform administrators

**Key Features:**
- 3rd Normal Form (3NF) normalization
- 20+ foreign key relationships
- 35+ optimized indexes
- ACID-compliant transactions
- Referential integrity enforcement


## 👥 User Roles

### Students
- Browse alumni directory
- Request mentorship
- RSVP to events
- Browse and apply for jobs
- Donate to campaigns
- Private messaging

### Alumni
- All student features
- Mentor students
- Post job opportunities
- Access batch/department groups
- Upload to memory gallery
- Group chat participation

### Administrators
- User management and approval
- Create and manage events
- Moderate content
- Manage fundraising campaigns
- Platform analytics


## 🔐 Security Features

- Password hashing with bcrypt
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- CSRF token protection
- Role-based access control
- File upload validation
- Session management

## 📈 Database Performance

- Indexed foreign keys for fast joins
- Optimized queries with EXPLAIN
- Denormalized counters (likes_count)
- Efficient pagination
- Transaction support for data integrity

## 🎓 Learning Outcomes

This project demonstrates:
- ✅ Database design and normalization
- ✅ ER modeling and relationships
- ✅ SQL query optimization
- ✅ Transaction management
- ✅ Foreign key constraints
- ✅ Indexing strategies
- ✅ Full-stack web development
- ✅ Payment gateway integration
- ✅ Real-world application architecture

## ‍💻 Author

**Molla Omar**
- Student ID: 0112310485
- Course: Database Management Systems Lab
- University: United International University
- trimester:9th
