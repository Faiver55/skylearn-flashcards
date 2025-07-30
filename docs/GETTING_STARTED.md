# SkyLearn Flashcards - Getting Started Guide

Welcome to SkyLearn Flashcards! This guide will help you get up and running quickly with creating your first interactive flashcard sets.

## Table of Contents

1. [Installation](#installation)
2. [First Steps](#first-steps)
3. [Creating Your First Flashcard Set](#creating-your-first-flashcard-set)
4. [Displaying Flashcards](#displaying-flashcards)
5. [Customization](#customization)
6. [Premium Features](#premium-features)
7. [Support](#support)

## Installation

### Automatic Installation (Recommended)

1. Login to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "SkyLearn Flashcards"
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate Plugin**

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern web browser with JavaScript enabled

## First Steps

### Welcome Screen

After activation, you'll be redirected to the SkyLearn Flashcards welcome screen. This provides quick links to:

- Create your first flashcard set
- Configure plugin settings
- Access documentation

### Initial Configuration

1. Go to **Flashcards > Settings**
2. Configure your basic settings:
   - **Colors:** Customize the appearance to match your site
   - **Behavior:** Set default animations and interactions
   - **Analytics:** Enable usage tracking (recommended)

## Creating Your First Flashcard Set

### Step 1: Add New Set

1. Navigate to **Flashcards > Add New**
2. Enter a descriptive title (e.g., "Spanish Vocabulary - Lesson 1")
3. Add an optional description
4. Set categories and tags for organization

### Step 2: Add Flashcards

1. Click **Add Card** to create your first flashcard
2. Enter the **Question** (front of the card)
3. Enter the **Answer** (back of the card)
4. Optionally add a **Hint**
5. Set the **Difficulty** level (Easy, Medium, Hard)
6. Click **Add Another Card** to continue adding more

### Step 3: Configure Set Settings

- **Shuffle Cards:** Randomize the order when displayed
- **Show Hints:** Display hints when available
- **Auto Advance:** Automatically move to next card after answering
- **Theme:** Choose from available visual themes

### Step 4: Publish

1. Click **Save Draft** to save your work
2. Preview your flashcards using the **Preview** button
3. When ready, click **Publish** to make it live

## Displaying Flashcards

### Using Shortcodes

The easiest way to display flashcards is using shortcodes:

```
[skylearn_flashcards id="123"]
```

#### Shortcode Parameters

- `id` - Flashcard set ID (required)
- `show_progress` - Show progress bar (true/false)
- `shuffle` - Randomize card order (true/false)
- `theme` - Visual theme (default, modern, minimal, dark)
- `max_cards` - Limit number of cards shown
- `difficulty` - Filter by difficulty (all, easy, medium, hard)

#### Examples

```
[skylearn_flashcards id="123" shuffle="true" theme="modern"]
[skylearn_flashcards id="456" max_cards="10" difficulty="easy"]
```

### Using Gutenberg Blocks

1. Add a new block in the WordPress editor
2. Search for "SkyLearn Flashcards"
3. Select your flashcard set from the dropdown
4. Configure display options in the block settings

### Finding Set IDs

- Go to **Flashcards > All Sets**
- The ID is shown in the **Shortcode** column
- Or check the URL when editing a set

## Customization

### Appearance Settings

1. Go to **Flashcards > Settings > Appearance**
2. Customize colors:
   - **Primary Color:** Main brand color
   - **Accent Color:** Highlights and buttons
   - **Background Color:** Card backgrounds
   - **Text Color:** Text content

### Behavior Settings

1. Go to **Flashcards > Settings > Behavior**
2. Configure:
   - **Progress Display:** Show/hide progress indicators
   - **Keyboard Navigation:** Enable keyboard shortcuts
   - **Touch Gestures:** Enable swipe gestures on mobile
   - **Autoplay Interval:** Time between cards in autoplay mode

### Keyboard Shortcuts

- **Spacebar:** Flip current card
- **Left Arrow:** Previous card
- **Right Arrow:** Next card
- **K:** Mark card as known
- **U:** Mark card as unknown

## Premium Features

Upgrade to SkyLearn Flashcards Premium to unlock advanced features:

### Advanced Analytics
- Detailed usage reports
- Student progress tracking
- Performance insights
- Export capabilities

### Lead Collection
- Built-in contact forms
- Email marketing integrations
- Automated follow-up sequences
- Lead management dashboard

### LMS Integration
- Seamless LearnDash integration
- TutorLMS compatibility
- Grade book synchronization
- Progress tracking

### Enhanced Export
- Bulk export/import
- Multiple file formats
- Backup and restore
- Content migration tools

### Get Premium

Visit [https://skyian.com/skylearn-flashcards/premium/](https://skyian.com/skylearn-flashcards/premium/) to upgrade.

## Tips and Best Practices

### Creating Effective Flashcards

1. **Keep it simple:** One concept per card
2. **Use clear language:** Avoid ambiguous questions
3. **Include context:** Provide enough information to answer
4. **Add hints:** Help users when they're stuck
5. **Vary difficulty:** Mix easy and challenging cards

### Organization

1. **Use categories:** Group related content
2. **Add tags:** Enable filtering and search
3. **Descriptive titles:** Make sets easy to find
4. **Regular updates:** Keep content fresh and accurate

### Performance

1. **Limit set size:** 10-20 cards per set for best experience
2. **Optimize images:** Use compressed images if adding visual content
3. **Test regularly:** Preview sets before publishing
4. **Monitor analytics:** Track usage and adjust accordingly

## Troubleshooting

### Common Issues

**Flashcards not displaying:**
- Check that the set ID is correct
- Ensure the set is published
- Verify shortcode syntax

**Styling issues:**
- Clear browser cache
- Check for theme conflicts
- Review custom CSS

**Performance problems:**
- Reduce number of cards per set
- Disable unnecessary features
- Check hosting performance

### Getting Help

1. **Documentation:** [https://skyian.com/skylearn-flashcards/docs/](https://skyian.com/skylearn-flashcards/docs/)
2. **Support Forum:** [https://skyian.com/support/](https://skyian.com/support/)
3. **Email Support:** support@skyian.com
4. **Video Tutorials:** [https://skyian.com/skylearn-flashcards/tutorials/](https://skyian.com/skylearn-flashcards/tutorials/)

## Next Steps

Now that you're set up, explore these advanced features:

1. **Import/Export:** Migrate content from other tools
2. **Analytics:** Monitor student engagement
3. **Integrations:** Connect with your LMS
4. **Customization:** Match your brand styling
5. **Premium Upgrade:** Unlock advanced features

Happy teaching with SkyLearn Flashcards! 🎓✨

---

*Need more help? Don't hesitate to reach out to our support team at support@skyian.com*