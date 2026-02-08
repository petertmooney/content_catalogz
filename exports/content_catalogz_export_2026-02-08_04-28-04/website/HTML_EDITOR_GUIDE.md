# Using the HTML Page Editor

The admin dashboard now includes a powerful HTML page editor that allows you to edit your website's pages directly through the browser.

## Accessing the Editor
   v
1. Log in to the admin panel at `/admin/login.php`
2. Click on **"📝 Edit Pages"** in the sidebar
3. You'll see a list of all HTML files in your website

## Editing a Page

1. Click the **"Edit"** button next to any HTML file
2. The file content will open in a code editor
3. Make your changes to the HTML code
4. Click **"Save Changes"** when done

### What You Can Edit

You can edit any `.html` file in your website's root directory:
- `index.html` - Home page
- `about.html` - About page
- `quote.html` - Quote/Contact page
- Any other HTML files you create

## Safety Features

### Automatic Backups
Every time you save a file, a backup is automatically created in the `/backups` directory with a timestamp:
- Format: `filename.YYYY-MM-DD_HH-mm-ss.backup`
- Example: `index.html.2026-02-07_14-30-45.backup`

### How to Restore a Backup

If you make a mistake, you can restore a previous version:

**Option 1: Through File System**
```bash
# Copy backup to original file
cp backups/index.html.2026-02-07_14-30-45.backup index.html
```

**Option 2: Manual Restore**
1. Open the backup file from `/backups` directory
2. Copy its contents
3. Open the editor for the file you want to restore
4. Paste the backup content
5. Save

## Tips for Editing

### Before You Edit
- Always preview the current page first (click "View" button)
- Make sure you understand what section you're editing
- For major changes, consider downloading a manual backup first

### While Editing
- The editor preserves formatting and whitespace
- Use proper HTML syntax
- Test your changes frequently
- Save often (each save creates a new backup)

### After Editing
- Click "View" to see your changes live
- Check the page in different screen sizes
- Verify all links still work

## Common Editing Tasks

### Changing Text Content
Find the text you want to change in the HTML and modify it:
```html
<h1>Old Heading</h1>
<!-- Change to: -->
<h1>New Heading</h1>
```

### Updating Links
Find the `<a>` tag and modify the `href` attribute:
```html
<a href="old-page.html">Link</a>
<!-- Change to: -->
<a href="new-page.html">Link</a>
```

### Changing Images
Find the `<img>` tag and modify the `src` attribute:
```html
<img src="old-image.jpg" alt="Description">
<!-- Change to: -->
<img src="new-image.jpg" alt="Description">
```

### Adding a New Section
Copy an existing section and modify it:
```html
<section class="content-section">
    <div class="container">
        <h2>Your New Section</h2>
        <p>Your content here</p>
    </div>
</section>
```

## Security

### Who Can Edit?
- Only logged-in admin users can access the editor
- Files are validated to prevent directory traversal attacks
- Only `.html` files in the root directory can be edited

### What Files Are Protected?
The editor cannot modify:
- PHP files
- Configuration files
- Files outside the root directory
- Admin panel files

## Troubleshooting

### Changes Not Showing
- Clear your browser cache (Ctrl+F5 or Cmd+Shift+R)
- Make sure you clicked "Save Changes"
- Check that no HTML syntax errors were introduced

### File Won't Save
- Check file permissions on the server
- Ensure the `/backups` directory is writable
- Look for PHP errors in the browser console

### Broke the Layout
1. Don't panic - you have backups!
2. Go to `/backups` directory
3. Find the most recent backup before your change
4. Restore it using the steps above

## Best Practices

1. **Test in Stages**: Make small changes and test frequently
2. **Backup Before Major Changes**: Download a copy before big edits
3. **Use Consistent Formatting**: Keep your HTML clean and readable
4. **Document Changes**: Keep notes of what you changed and why
5. **Regular Cleanup**: Periodically remove old backups to save space

## Advanced Usage

### Bulk Changes
If you need to make the same change across multiple files:
1. Edit the first file
2. Copy the changed section
3. Open the next file
4. Paste and adjust as needed
5. Repeat for all files

### Working with CSS
While you can modify inline styles in HTML files, remember:
- Main styles are in`/assets/css/styles.css`
- Consider editing CSS file for design changes
- Keep HTML focused on content and structure

---

**Need Help?** 
- Check the [ADMIN_SETUP.md](ADMIN_SETUP.md) for setup information
- See [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) for technical details
- Contact your developer if you're unsure about making changes
