# 🎓 MiniMinds Academy

A robust PHP gamified learning platform designed for children aged 4-9, featuring coding basics, business fundamentals, and interactive storytelling.

## 🌟 Features

### For Kids (Ages 4-9)

- **Interactive Learning**: Engaging stories and games that teach coding and business concepts
- **Digital Library**: 100+ educational books with interactive features and audio narration
- **Reading Adventures**: Age-appropriate stories across 10 categories including science, math, and business
- **Gamification System**: Points, badges, levels, and achievements
- **Quest System**: Daily and weekly challenges to keep learning exciting
- **Virtual Store**: Earn points to purchase avatars, pets, and decorations
- **Kid-Friendly Interface**: Colorful, intuitive design with large buttons and fun animations
- **Voice Narration**: Audio support for pre-readers and narrated books
- **Progress Tracking**: Visual progress bars and achievement celebrations
- **Interactive Books**: Clickable vocabulary, puzzles, and comprehension activities

### For Parents

- **Comprehensive Dashboard**: Monitor all children's progress from one place
- **Parental Controls**: Set time limits, content restrictions, and purchase approvals
- **Detailed Reports**: Weekly progress reports and learning analytics
- **Family Plans**: Support for multiple children under one subscription
- **Email Notifications**: Real-time updates on achievements and milestones
- **Safe Environment**: Ad-free, COPPA-compliant platform

### Platform Features

- **Digital Library**: Comprehensive reading platform with interactive educational books
- **Reading Progress Tracking**: Detailed analytics for parents and motivation for kids
- **Audio Books**: Narrated stories for pre-readers and listening comprehension
- **Interactive Elements**: Clickable vocabulary, puzzles, and educational activities
- **Book Recommendations**: AI-powered suggestions based on reading level and interests
- **Reading Goals**: Customizable goals with rewards and achievement tracking
- **Freemium Model**: Core content free, premium features with subscription
- **Responsive Design**: Works perfectly on tablets, phones, and computers
- **Security First**: Child safety features, data encryption, and secure authentication
- **Scalable Architecture**: Built on PHP with MySQL for reliability and performance
- **Easy Installation**: Automated setup wizard with step-by-step instructions

## 🚀 Quick Start

### Requirements

- XAMPP (or similar PHP/MySQL environment)
- PHP 7.4+ with PDO extension
- MySQL 5.7+ or MariaDB 10.2+
- Modern web browser with JavaScript enabled

### Installation

1. **Download and Extract**
   ```
   Download the miniminds-academy.zip file
   Extract to your XAMPP htdocs directory
   Rename folder to "miniminds-academy"
   ```

2. **Database Setup**
   ```
   Start XAMPP Apache and MySQL services
   Go to http://localhost/phpmyadmin
   Create new database named "miniminds_academy"
   Import the database/miniminds.sql file
   ```

3. **Configure Database**
   ```
   Open includes/config.php
   Update database credentials if needed:
   - DB_HOST: 'localhost' (usually unchanged)
   - DB_NAME: 'miniminds_academy'
   - DB_USER: 'root' (your MySQL username)
   - DB_PASS: '' (your MySQL password)
   ```

4. **Set Permissions**
   ```
   Make sure the following folders are writable:
   - logs/ (create this folder)
   - assets/uploads/ (create this folder)
   ```

5. **Access the Platform**
   ```
   Open your browser and go to: http://localhost/miniminds-academy
   ```

### Default Admin Account
- Username: `admin`
- Email: `admin@miniminds.com`
- Password: `admin123`

### Default Parent Account
- Username: `testparent`
- Email: `parent@example.com`
- Password: `parent123`

## 💰 Monetization Model

### Subscription Tiers

- **Free Plan**: 1 child, 5 lessons, basic progress tracking
- **Premium Plan**: ₦500/month - 2 children, unlimited lessons, advanced reports
- **Family Plan**: ₦1,500/month - 5 children, all features, priority support

### Revenue Streams

- Monthly subscriptions
- In-app virtual purchases
- Brand partnerships and sponsored content
- Educational content licensing

## 🛡️ Security Features

### Child Safety

- COPPA compliance measures
- Content filtering and moderation
- Parental verification required
- Safe chat/messaging (if enabled)
- Time limits and usage controls

### Platform Security

- Encrypted password storage
- CSRF protection
- SQL injection prevention
- Session management
- Rate limiting on login attempts
- Security headers implementation

### Data Privacy

- Minimal data collection
- Secure data transmission
- Regular security updates
- Parental consent requirements
- Data retention policies

## 📊 Analytics & Reporting

### Real-time Dashboard

- Active users and engagement metrics
- Course completion rates
- Revenue tracking
- System performance monitoring

### Parent Reports

- Weekly progress summaries
- Learning time analytics
- Achievement tracking
- Skill development insights

### Administrative Reports

- User growth metrics
- Subscription analytics
- Content performance
- Financial reporting

## 📱 Mobile Compatibility

### Responsive Design

- Touch-friendly interface
- Optimized for tablets
- Mobile browser support
- Progressive web app features

### Offline Functionality

- Downloadable content
- Offline progress tracking
- Sync capabilities
- Cache management

## 🎮 Learning Content

### Course Categories

- **Coding Basics**: Introduction to programming concepts through stories
- **Business Fundamentals**: Entrepreneurship and financial literacy
- **Problem Solving**: Critical thinking and logic puzzles
- **Creativity**: Art, music, and creative expression

### Lesson Types

- **Interactive Stories**: Choose-your-own-adventure narratives
- **Educational Games**: Fun challenges that teach concepts
- **Interactive Exercises**: Hands-on coding activities
- **Quizzes**: Knowledge checks with immediate feedback
- **Video Lessons**: Educational content with animations

### Progression System

- **Points**: Earned for completing lessons and quests
- **Levels**: Unlock new content as skills improve
- **Badges**: Achievement rewards for milestones
- **Leaderboards**: Friendly competition among peers
- **Streaks**: Daily engagement incentives

## 🛠️ Technical Architecture

### Backend Technologies

- **PHP 7.4+**: Core application logic
- **MySQL 5.7+**: Database management
- **PDO**: Secure database operations
- **Sessions**: User authentication and state management

### Frontend Technologies

- **Bootstrap 5**: Responsive UI framework
- **Font Awesome**: Icon library
- **jQuery**: JavaScript utilities
- **Custom CSS**: Kid-friendly styling
- **Vanilla JavaScript**: Interactive features

### Security Measures

- **Prepared Statements**: SQL injection prevention
- **Password Hashing**: bcrypt with salt
- **CSRF Tokens**: Cross-site request forgery protection
- **Input Sanitization**: XSS prevention
- **Session Security**: Secure cookie handling

### File Structure

```
miniminds-academy/
├── assets/
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   ├── images/       # Images and media
│   └── sounds/       # Sound effects
├── includes/         # Core PHP files
├── parents/          # Parent dashboard pages
├── kids/             # Children's interface
├── admin/            # Administrative interface
├── api/              # API endpoints
├── database/         # Database files
└── index.php         # Homepage
```

## 👥 User Roles

### Parents

- Register and manage family accounts
- Monitor children's progress and activities
- Set parental controls and time limits
- Upgrade subscription plans
- View detailed reports and analytics

### Children

- Login with simple username (no password required)
- Access age-appropriate learning content
- Earn points and unlock achievements
- Customize avatars and profiles
- Complete quests and challenges

## 🔧 Customization

### Theming

- Customizable color schemes
- Brand logo integration
- Font selection options
- Layout preferences

### Content Management

- Add/edit courses and lessons
- Upload educational content
- Manage achievement badges
- Configure virtual store items

### Integration Options

- Payment gateway integration
- Email service configuration
- Analytics tracking setup
- Social media integration

## 📈 Scaling & Performance

### Optimization Features

- Database indexing
- Caching mechanisms
- Image optimization
- Code minification

### Hosting Requirements

- Shared hosting for small deployments
- VPS for medium scale
- Cloud hosting for enterprise
- CDN integration for global access

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Check MySQL service is running
   - Verify database credentials in config.php
   - Ensure database exists and permissions are correct

2. **Permission Errors**
   - Set proper folder permissions (755 for directories, 644 for files)
   - Create logs/ directory if missing
   - Check .htaccess file configuration

3. **Session Issues**
   - Ensure PHP session path is writable
   - Check cookie settings in browser
   - Verify session configuration in php.ini

### Debug Mode

Enable debug mode by modifying `includes/config.php`:
```php
// Set to false in production
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Support & Documentation

### Getting Help

- **Documentation**: Check this README and inline code comments
- **Community**: Join our developer community
- **Email Support**: support@miniminds.com
- **Knowledge Base**: Comprehensive guides and tutorials

### Contributing

We welcome contributions! Please see our contributing guidelines for:
- Code standards
- Pull request process
- Issue reporting
- Feature requests

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Bootstrap Team for responsive framework
- Font Awesome for icon library
- PHP Community for robust backend
- Educational content contributors
- Parent and child testers

---

**MiniMinds Academy** - Making learning fun and engaging for the next generation! 🌟

Built with ❤️ for young learners everywhere.