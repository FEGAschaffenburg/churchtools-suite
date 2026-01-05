# v0.10.4.11 - Tag Filtering & Display

**Release Date:** 5. Januar 2026  
**Type:** Feature  
**Branch:** production → deployment

---

## 🎯 Summary

Tag-Filterung und -Anzeige für Events implementiert. Events können jetzt nach Tags gefiltert werden (AND-Logik) und Tags können als farbige Badges angezeigt werden.

**Impact:** Bessere Event-Organisation und visuelle Tag-Darstellung

---

## ✨ New Features

### 1. Tag Filtering (AND-Logik)

**Shortcode Parameter:** `filter_tags="Gottesdienst,Alpha"`

```
[cts_events_list filter_tags="Gottesdienst,Alpha"]
[cts_grid filter_tags="Gottesdienst" limit="10"]
```

**Logik:** Event muss **ALLE** angegebenen Tags haben (AND-Verknüpfung)

**Beispiel:**
- Event mit Tags `["Gottesdienst"]` → ❌ nicht angezeigt
- Event mit Tags `["Gottesdienst", "Alpha"]` → ✅ angezeigt
- Event mit Tags `["Gottesdienst", "Alpha", "Livestream"]` → ✅ angezeigt

**Features:**
- Case-insensitive Matching
- Komma-separierte Liste
- Post-Processing nach Database-Query
- Funktioniert mit allen List/Grid-Views

---

### 2. Tag Display

**Shortcode Parameter:** `show_tags="true"`

```
[cts_events_list show_tags="true"]
[cts_grid show_tags="true"]
```

**Features:**
- Farbige Badges (nutzt Tag-Color aus ChurchTools)
- Responsive Design
- Default: `false` (opt-in)
- CSS-Klassen: `.cts-list-tags`, `.cts-tag-badge`

**Styling:**
```css
.cts-tag-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    color: #fff;
    background-color: <tag.color>; /* From ChurchTools */
}
```

---

### 3. Combined Usage

```
[cts_events_list 
    calendar_ids="2" 
    filter_tags="Gottesdienst" 
    show_tags="true" 
    limit="10"
]
```

---

## 🔧 Technical Implementation

### Files Changed

**1. Shortcodes (`includes/class-churchtools-suite-shortcodes.php`):**
- Added `filter_tags` parameter to list/grid shortcodes
- Added `show_tags` parameter to list/grid shortcodes
- New helper: `parse_tag_filter()` - converts comma-separated string to array

**2. Template Data Provider (`includes/services/class-churchtools-suite-template-data.php`):**
- New method: `filter_events_by_tags()` - AND-logic filtering
- New method: `parse_tags()` - JSON to array conversion
- Enhanced `format_event()` - adds `tags_array` field
- Added `filter_tags` to query filters

**3. Templates:**
- `templates/list/classic.php` - Tag badges rendering
- Added `$show_tags` parameter parsing

**4. CSS (`assets/css/churchtools-suite-public.css`):**
- `.cts-list-tags` - Container styling
- `.cts-tag-badge` - Badge styling

---

## 📋 Usage Examples

### Filter by Single Tag
```
[cts_events_list filter_tags="Gottesdienst"]
```

### Filter by Multiple Tags (AND)
```
[cts_events_list filter_tags="Gottesdienst,Livestream"]
```
Shows only events that have **BOTH** tags.

### Display Tags
```
[cts_events_list show_tags="true"]
```

### Combined: Filter + Display
```
[cts_events_list 
    calendar_ids="2" 
    filter_tags="Gottesdienst" 
    show_tags="true" 
    limit="5"
]
```

### Grid View with Tags
```
[cts_grid 
    columns="3" 
    filter_tags="Alpha,Workshop" 
    show_tags="true"
]
```

---

## 🎨 Template Data Structure

Events now include `tags_array` field:

```php
$event['tags_array'] = [
    [
        'id' => 34,
        'name' => 'Gottesdienst',
        'description' => null,
        'color' => '#6b7280',
        'count' => 1
    ],
    // ...
];
```

**Template Usage:**
```php
<?php if ( $show_tags && ! empty( $event['tags_array'] ) ) : ?>
    <div class="cts-list-tags">
        <?php foreach ( $event['tags_array'] as $tag ) : ?>
            <span class="cts-tag-badge" style="background-color: <?php echo esc_attr( $tag['color'] ); ?>;">
                <?php echo esc_html( $tag['name'] ); ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

---

## 🔍 AND-Logic Explanation

**Why AND-Logic?**
- More precise filtering
- Find events that match specific combinations
- Example: "Gottesdienst + Livestream" finds only livestreamed services

**Future Enhancement:**
Could add `filter_tags_logic="or"` parameter for OR-filtering:
- `filter_tags="Gottesdienst,Alpha" filter_tags_logic="and"` (default)
- `filter_tags="Gottesdienst,Alpha" filter_tags_logic="or"` (future)

---

## 🧪 Testing Checklist

**Developer Testing:**
- [x] Tag filtering works (AND-logic verified)
- [x] Tag display renders correctly
- [x] CSS styling applied
- [x] Case-insensitive matching
- [x] Empty tags handled gracefully

**User Testing Required:**
- [ ] Test with real ChurchTools tags
- [ ] Verify tag colors from ChurchTools
- [ ] Test filter_tags with multiple tags
- [ ] Test show_tags in different templates
- [ ] Check responsive design on mobile

**Expected Database:**
- Events with tags: `tags` column has JSON array
- Events without tags: `tags` column is NULL

---

## 📚 Related Features

**Depends On:**
- v0.10.4.9: Tags import from ChurchTools (CRITICAL FIX)
- v0.9.2.0: Tags database column added

**Future Enhancements:**
- OR-logic filtering option
- Tag cloud shortcode
- Tag-based navigation
- Tag statistics

---

## 🚀 Deployment

**Files Changed:**
- `includes/class-churchtools-suite-shortcodes.php`
- `includes/services/class-churchtools-suite-template-data.php`
- `templates/list/classic.php`
- `assets/css/churchtools-suite-public.css`
- `churchtools-suite.php` (version bump)

**Database Changes:** None

**Migration Required:** No

**Backwards Compatible:** Yes (show_tags defaults to false)

**Breaking Changes:** None

---

**Previous Version:** v0.10.4.10  
**Next Milestone:** v0.11.0 (Advanced Filtering)
