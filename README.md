# SkyLearn Flashcards

![SkyLearn Flashcards Logo](assets/img/logo-horiz.png)

**The Ultimate WordPress Flashcards Plugin for Educational Excellence**

[![Beta Version](https://img.shields.io/badge/Version-1.0.0--beta-orange.svg)](https://github.com/Faiver55/skylearn-flashcards)
[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.4+-blue.svg)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Beta Testing](https://img.shields.io/badge/Status-Beta%20Testing-yellow.svg)](https://github.com/Faiver55/skylearn-flashcards)

## 🧪 **BETA RELEASE** - We Need Your Feedback!

> **Welcome to the SkyLearn Flashcards Beta Program!** This is a pre-release version designed for testing and feedback. If you're a beta tester, thank you for helping us make this plugin better. If you're discovering this for the first time, please note this is **not the final release**.

### 🎯 Beta Testing Program
- **Current Version**: 1.0.0-beta
- **Beta Duration**: 4-6 weeks
- **Feedback Deadline**: [See beta communication]
- **Public Launch**: Coming soon after beta completion

**👥 Are you a beta tester?** Start with our [Beta Onboarding Guide](docs/ONBOARDING.md)

**🐛 Found an issue?** Use our [Feedback Template](docs/FEEDBACK_TEMPLATE.md)

**📧 Questions?** Email us at support@skyian.com

---

## 🚀 Overview

SkyLearn Flashcards is a premium WordPress plugin designed for teachers, students, schools, and online academies. Create interactive flashcard sets with seamless LMS integration, advanced reporting, and professional branding.

**Developed by:** [Skyian LLC](https://skyian.com/)  
**Author:** Ferdous Khalifa  
**Support:** support@skyian.com  
**Website:** https://skyian.com/skylearn-flashcards/

## ✨ Key Features

### 🎯 Core Features (Free Version)
- **Interactive Flashcard Sets** - Create and manage unlimited flashcard collections
- **Shortcode & Block Support** - Easy embedding with `[skylearn_flashcards]` shortcode
- **Responsive Design** - Perfect display on all devices
- **Basic Analytics** - Track study progress and performance
- **Customizable Styling** - Match your site's branding

### 🎓 LMS Integration
- **LearnDash Compatibility** - Seamless integration with course content
- **TutorLMS Support** - Enhanced learning management features
- **Progress Tracking** - Student performance monitoring

### 💎 Premium Features (Available in Beta)
- **Advanced Reporting** - Detailed analytics and insights
- **Lead Collection** - Capture student information
- **Bulk Export** - Export flashcard sets and data
- **Email Integration** - Mailchimp, Vbout, SendFox support
- **Unlimited Sets** - No restrictions on flashcard collections
- **Priority Support** - Direct access to our support team

> **🎁 Beta Bonus**: Premium features are fully unlocked during the beta period!

## 🛠️ Installation

### 🧪 Beta Installation (Current)

**For Beta Testers:**
1. **Download** the beta ZIP file from your invitation email
2. **Go to** Plugins > Add New > Upload Plugin in WordPress admin
3. **Upload** `skylearn-flashcards-beta.zip`
4. **Activate** and follow the welcome wizard
5. **Start testing** with our [Beta Onboarding Guide](docs/ONBOARDING.md)

### 📦 Standard Installation (After Public Release)

#### Method 1: WordPress Admin Dashboard
1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

#### Method 2: Manual Installation
1. Extract the plugin files to `/wp-content/plugins/skylearn-flashcards/`
2. Activate the plugin through the **Plugins** menu in WordPress

#### Method 3: WordPress.org Repository
```bash
# Available after public launch
```

## 🚀 Quick Start Guide

### Step 1: Create Your First Flashcard Set
1. Navigate to **SkyLearn > Flashcards** in your WordPress admin
2. Click **Add New Set**
3. Enter your flashcard questions and answers
4. Configure display settings
5. Save your set

### Step 2: Display Flashcards
Use the shortcode to display your flashcard sets:
```php
[skylearn_flashcards id="123"]
```

Or use the Gutenberg block:
1. Add a new block
2. Search for "SkyLearn Flashcards"
3. Select your flashcard set

### Step 3: Customize Appearance
1. Go to **SkyLearn > Settings**
2. Adjust colors, fonts, and layout options
3. Preview changes in real-time

## 📋 System Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **Memory:** 128MB minimum (256MB recommended)

## 🎨 Brand Guidelines

### Color Scheme
- **Primary:** #3498db (Sky Blue)
- **Accent:** #f39c12 (Soft Orange)
- **Background:** #f8f9fa (Light Gray)
- **Text:** #222831 (Dark Slate)

### Logo Assets
- Horizontal Logo: `assets/img/logo-horiz.png`
- Icon Logo: `assets/img/logo-icon.png`

## 📈 Development Roadmap

### Phase 1: Plugin Skeleton & Initial Setup ✅
- [x] Plugin structure and constants
- [x] Main plugin class
- [x] Activation/deactivation hooks
- [x] Internationalization support

### Phase 2: Core Features (Free Version)
- [ ] Flashcard set creation interface
- [ ] Frontend display system
- [ ] Shortcode implementation
- [ ] Basic styling options
- [ ] Simple analytics

### Phase 3: LMS Integration
- [ ] LearnDash integration
- [ ] TutorLMS compatibility
- [ ] Progress tracking
- [ ] Grade book integration

### Phase 4: Lead Collection & Premium Features
- [ ] Lead capture forms
- [ ] Email integration (Mailchimp, Vbout, SendFox)
- [ ] Premium licensing system
- [ ] Advanced user management

### Phase 5: Advanced Reporting (Premium)
- [ ] Detailed analytics dashboard
- [ ] Performance insights
- [ ] Export capabilities
- [ ] Custom reports

### Phase 6: Bulk Export & Unlimited Sets (Premium)
- [ ] Bulk data export
- [ ] Import/export flashcard sets
- [ ] Unlimited flashcard collections
- [ ] Advanced search and filtering

### Phase 7: UI/UX Polish & Branding
- [ ] Professional design system
- [ ] Accessibility improvements
- [ ] Mobile optimization
- [ ] Custom branding options

### Phase 8: Testing & QA
- [ ] Comprehensive testing suite
- [ ] Performance optimization
- [ ] Security audit
- [ ] Cross-browser compatibility

### Phase 9: Beta Launch
- [ ] Limited beta release
- [ ] User feedback collection
- [ ] Bug fixes and improvements
- [ ] Documentation completion

### Phase 10: Marketplace Submission & Premium Launch
- [ ] WordPress.org submission
- [ ] Envato Market listing
- [ ] Premium version launch
- [ ] Marketing campaign

## 🤝 Contributing

We welcome contributions to SkyLearn Flashcards! Please read our [contributing guidelines](docs/CONTRIBUTING.md) before submitting pull requests.

### Development Setup
```bash
# Clone the repository
git clone https://github.com/Faiver55/skylearn-flashcards.git

# Install dependencies
composer install
npm install

# Set up development environment
npm run dev
```

## 📚 Documentation

- [User Guide](docs/USER_GUIDE.md)
- [Developer Documentation](docs/DEVELOPER.md)
- [API Reference](docs/API.md)
- [Changelog](docs/CHANGELOG.md)

## 🐛 Bug Reports & Feature Requests

Found a bug or have a feature request? Please open an issue on our [GitHub repository](https://github.com/Faiver55/skylearn-flashcards/issues).

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🏢 About Skyian LLC

SkyLearn Flashcards is proudly developed by [Skyian LLC](https://skyian.com/), a technology company focused on creating innovative educational tools and solutions.

**Contact Information:**
- Website: https://skyian.com/
- Email: support@skyian.com
- Plugin Page: https://skyian.com/skylearn-flashcards/
- Privacy Policy: https://skyian.com/skylearn-flashcards/privacy-policy/
- Terms of Service: https://skyian.com/skylearn-flashcards/tos/

---

*Made with ❤️ for educators worldwide*
