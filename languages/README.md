# Jankx Lunar Calendar Translations

## How to Compile Translations

### Method 1: Using Helper Scripts (Easiest) ⭐
Windows:
```cmd
compile-mo.bat
```

Linux/Mac/Git Bash:
```bash
./compile-mo.sh
```

### Method 2: Using msgfmt (Git for Windows)
```bash
"C:\Program Files\Git\usr\bin\msgfmt.exe" -o lunar-calendar-vi.mo lunar-calendar-vi.po --statistics
```

### Method 3: Using WP-CLI
```bash
wp i18n make-mo languages/
```

### Method 4: Using PHP Script
```bash
php ../compile-translations.php
```

### Method 5: Using Poedit
1. Open the `.po` file in Poedit
2. Click "Save" - the `.mo` file will be automatically generated

### Method 6: Using Loco Translate Plugin
1. Install and activate Loco Translate plugin
2. Go to Loco Translate > Themes/Plugins
3. Find "Jankx Lunar Calendar"
4. Edit or sync the translation
5. Save - the `.mo` file will be automatically generated

## Available Translations

- Vietnamese (vi): `lunar-calendar-vi.po`

## Creating New Translations

1. Copy `lunar-calendar.pot` to `lunar-calendar-{locale}.po`
2. Translate all strings in the `.po` file
3. Compile to `.mo` using one of the methods above

