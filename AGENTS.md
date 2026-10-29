# Agent Instructions

## Translations (l10n)

Translation files are in the `l10n/` directory. Each language has two files:
- `<lang>.json` — used by the PHP backend (`$this->translate->t('...')`)
- `<lang>.js` — used by the Vue/JS frontend (`t('vtramit', '...')`)

### Available languages

| File prefix | Language  |
|-------------|-----------|
| `ca`        | Catalan   |
| `de`        | German    |
| `es`        | Spanish   |
| `eu`        | Basque    |
| `gl`        | Galician  |
| `it`        | Italian   |
| `pt`        | Portuguese|

### File structure

**`.json`** files:
```json
{
  "translations": {
    "Key": "Translation",
    ...
    "Last key": "Last translation"
  },
  "pluralForm": "nplurals=2; plural=(n != 1);"
}
```

**`.js`** files:
```js
OC.L10N.register(
  "vtramit",
  {
    "Key": "Translation",
    ...
    "Last key": "Last translation"
  },
  "nplurals=2; plural=(n != 1);"
)
```

### Rules for adding new literals

1. **Check for duplicates first** — before adding a key to a file, search for it in that file. If the key already exists in a given language file, do NOT add it again and do NOT modify the existing translation for that language.
2. **Insert at the end**, just before the closing `}` of the translations block — do NOT insert alphabetically or in any other position.
3. **Apply to ALL language files** — both `.json` and `.js` for every language listed above (14 files total). Skip only those where the key already exists.
4. The English key must be identical in all files. Only the translated value differs per language.
5. When you don't know the correct translation for a language, use a reasonable translation based on the meaning. Do not leave any language without its translation.

### Example

To add the key `"New feature enabled"`:

In `es.json` and `es.js` (before the closing `}`):
```
"New feature enabled": "Nueva funcionalidad activada"
```

In `ca.json` and `ca.js`:
```
"New feature enabled": "Nova funcionalitat activada"
```

...and so on for all 7 languages.
