# Create Event Page - Documentation

## Overview

A comprehensive event creation interface for organizers to plan and book events with venues and services.

## Features

### ✅ **User Interface**

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern Layout**: Clean, professional design with Tailwind CSS
- **Interactive Elements**: Hover effects, transitions, and animations
- **Sticky Navigation**: Easy access to menu items
- **Sticky Summary**: Cost summary stays visible while scrolling

### ✅ **Form Sections**

#### 1. **Basic Information**

- **Event Name**: Required text field
- **Event Type**: Dropdown (Wedding, Corporate, Birthday, Concert, Other)
- **Theme**: Optional text field
- **Expected Guests**: Required number field
- **Event Date & Time**: Required datetime picker (prevents past dates)

#### 2. **Venue Selection**

- Visual card-based selection
- Shows venue details:
  - Name
  - Location
  - Capacity
  - Base price
- Interactive selection with visual feedback
- Radio button for selection
- Click anywhere on card to select

#### 3. **Services Selection**

- Organized by category:
  - 🍽️ Catering
  - 🎵 Lights and Sounds
  - 📸 Photography
  - 🎥 Videography
  - 🎤 Host/Emcee
  - 💐 Styling and Flowers
  - 🪑 Equipment Rental
- Checkbox-based selection
- Shows service details:
  - Service name
  - Supplier name
  - Location
  - Description
  - Price
- Multiple services can be selected

#### 4. **Cost Summary (Sidebar)**

- Real-time cost calculation
- Shows breakdown:
  - Venue cost
  - Services cost
  - Total cost
- Sticky sidebar stays visible
- Large, clear total amount display

### ✅ **Validation**

#### Client-Side Validation (JavaScript)

- ✅ All required fields must be filled
- ✅ Event name cannot be empty
- ✅ Event type must be selected
- ✅ Expected guests must be at least 1
- ✅ Event date must be in the future
- ✅ Venue must be selected
- ✅ Capacity warning: Alerts if guests exceed venue capacity
- ✅ User-friendly error messages

#### Server-Side Validation (PHP)

- ✅ User authorization check
- ✅ All required field validation
- ✅ Data type validation
- ✅ Future date validation
- ✅ Database integrity checks
- ✅ SQL injection prevention (prepared statements)

### ✅ **Functionality**

#### Real-Time Features

- **Live Cost Calculation**: Updates as user selects venue/services
- **Visual Feedback**: Selected items highlighted
- **Interactive Cards**: Hover effects and smooth transitions
- **Responsive Alerts**: Success/error messages with icons

#### Form Submission

- **AJAX Submission**: No page reload
- **Loading State**: Button shows spinner during submission
- **Transaction Safety**: Database transaction ensures data integrity
- **Error Handling**: Graceful error messages
- **Success Redirect**: Automatically redirects to dashboard after creation

### ✅ **Database Operations**

#### Tables Used

1. **events**: Main event record
2. **event_services**: Links events to selected services
3. **venues**: Venue information
4. **services**: Available services
5. **suppliers**: Service providers

#### Data Flow

```
1. User fills form →
2. Client-side validation →
3. AJAX POST to create-event-handler.php →
4. Server-side validation →
5. Begin transaction →
6. Insert event record →
7. Insert event_services records →
8. Commit transaction →
9. Return success response →
10. Redirect to dashboard
```

## File Structure

```
public/pages/organizer/
└── create-event.php          # Main page with form UI

public/assets/js/
└── create-event.js            # Client-side logic and validation

src/services/
└── create-event-handler.php   # Server-side form processing
```

## Usage

### For End Users

1. Navigate to "Create Event" from organizer dashboard
2. Fill in basic event information
3. Select a venue by clicking on a venue card
4. (Optional) Select services by checking desired options
5. Review the cost summary in the sidebar
6. Click "Create Event" button
7. Wait for confirmation message
8. Automatically redirected to dashboard

### For Developers

#### Adding New Event Types

Edit `create-event.php` line ~170:

```php
<option value="NewType">New Type Name</option>
```

#### Adding New Service Categories

1. Add data to `services` table with new category
2. Add icon to `create-event.php` around line ~245:

```php
$icons = [
    // ... existing icons
    'New Category' => '🎨'
];
```

#### Customizing Validation

Edit `create-event.js` function `validateForm()` around line ~120

#### Modifying Cost Calculation

Edit `create-event.js` function `updateCostSummary()` around line ~65

## API Endpoint

### POST `/src/services/create-event-handler.php`

**Request Body (FormData)**:

```
event_name: string (required)
event_type: string (required)
theme: string (optional)
expected_guests: number (required, min: 1)
event_date: datetime (required, future date)
venue_id: number (required)
total_cost: number (calculated automatically)
services[]: array of service_ids (optional)
```

**Response (JSON)**:

```json
{
  "success": true,
  "message": "Event created successfully!",
  "event_id": 123,
  "event_name": "Mike & Anna Wedding"
}
```

**Error Response**:

```json
{
  "success": false,
  "error": "Error message here",
  "details": "Detailed error information"
}
```

## Security Features

✅ **Session-Based Authentication**: Only logged-in organizers can access
✅ **CSRF Protection**: Session validation on each request
✅ **SQL Injection Prevention**: Prepared statements for all queries
✅ **XSS Prevention**: HTML escaping on all output
✅ **Input Validation**: Both client and server-side
✅ **Transaction Safety**: Atomic database operations

## Responsive Design

### Desktop (≥1024px)

- 3-column layout (2 columns for form, 1 for summary)
- Full venue and service cards displayed side-by-side

### Tablet (768px - 1023px)

- 2-column layout for form sections
- Single column for summary

### Mobile (<768px)

- Single column layout
- Stacked form elements
- Optimized touch targets

## Browser Support

✅ Chrome (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Edge (latest)
✅ Mobile browsers

## Future Enhancements

### Potential Features

- [ ] Save as draft functionality
- [ ] Event duplication
- [ ] Multiple date options
- [ ] Guest list management
- [ ] Budget planner integration
- [ ] Calendar view for date selection
- [ ] Real-time availability checking
- [ ] Email notifications
- [ ] Payment integration
- [ ] Contract generation
- [ ] Photo gallery upload

## Testing Checklist

- [ ] Create event with all fields
- [ ] Create event with minimum fields (no services, no theme)
- [ ] Verify cost calculation accuracy
- [ ] Test venue capacity warning
- [ ] Test past date validation
- [ ] Test with different service combinations
- [ ] Test form submission error handling
- [ ] Test redirect after successful creation
- [ ] Verify database records created correctly
- [ ] Test on mobile devices
- [ ] Test in different browsers

## Common Issues & Solutions

### Issue: Cost not updating

**Solution**: Check browser console for JavaScript errors. Ensure venue/service elements have correct `data-price` attributes.

### Issue: Form submission fails

**Solution**:

1. Check PHP error logs
2. Verify database connection
3. Ensure all required fields are filled
4. Check session is active

### Issue: Venue cards not clickable

**Solution**: Verify JavaScript is loaded and no conflicts with other scripts.

### Issue: Services not saving

**Solution**: Check `event_services` table structure and foreign key constraints.

## Performance Considerations

- **Lazy Loading**: Consider lazy loading venue images (if added)
- **Caching**: Venue and service data can be cached
- **Optimization**: Use database indexes on frequently queried fields
- **Pagination**: If venues/services list grows large, add pagination
- **CDN**: Use CDN for Font Awesome icons in production

## Maintenance

### Regular Tasks

- Monitor database for orphaned records
- Review and optimize queries
- Update service pricing
- Clean up old draft events (if feature added)
- Backup database regularly

### Code Quality

- Follow PSR standards for PHP
- Use ESLint for JavaScript
- Comment complex logic
- Keep functions small and focused
- Write descriptive variable names

---

**Created**: November 4, 2025
**Last Updated**: November 4, 2025
**Version**: 1.0.0
