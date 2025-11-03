# Organizer Dashboard Setup Guide

## Overview

The Organizer Dashboard is designed for event organizers to manage their events, find venues, and get AI-powered venue recommendations. This secure dashboard includes a prominent AI chatbot feature for intelligent venue matching.

## Features

### 🔐 Secure Authentication

- Role-based access control
- Session management with user verification
- Automatic redirection for unauthorized access
- Secure password handling

### 🤖 AI Recommendation Chatbot

- **Prominent Feature**: Highlighted banner on dashboard
- Natural language processing for event requirements
- Intelligent venue matching based on:
  - Event type (wedding, corporate, birthday, concert)
  - Number of guests
  - Budget constraints
  - Special amenities (parking, catering, sound, stage, AC, WiFi)
- Real-time chat interface
- Venue recommendations with suitability scores

### 📊 Dashboard Statistics

- My Events count
- Pending events
- Confirmed events
- Total spent tracking

### ⚡ Quick Actions

- Search venues
- Create new event
- View all events
- Manage bookings

### 📅 Recent Events

- View latest events
- Event status tracking
- Venue assignments
- Quick access to event details

## Installation Steps

### Step 1: Update Database Schema

Run the SQL script to add 'organizer' role to the database:

```bash
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select database: sad_db
3. Click "SQL" tab
4. Import file: db/add_organizer_role.sql
5. Click "Go"
```

This script will:

- Add 'organizer' to the role enum
- Create a test organizer account (username: organizer_juan, password: password123)

### Step 2: Verify File Structure

Ensure these files exist:

```
public/
  pages/
    organizer/
      organizer-dashboard.php
  assets/
    js/
      organizer.js
src/
  services/
    ai-recommendation.php
db/
  add_organizer_role.sql
```

### Step 3: Test the Dashboard

#### Create Organizer Account

1. Navigate to signup page
2. Select "Organizer" as role
3. Complete registration
4. Login with credentials

#### Or Use Test Account

- Username: `organizer_juan`
- Password: `password123`
- Email: `juan@organizer.com`

### Step 4: Test AI Chatbot

1. Login as organizer
2. Click "Start Chat" button on the AI banner
3. Try sample queries:
   - "I need a venue for 150 guests with a budget of 100000"
   - "Looking for a wedding venue for 200 people"
   - "Corporate event with 100 attendees, need parking and sound system"
   - "Birthday party for 80 guests, budget is 75000 pesos"

## Security Features

### Authentication Check

Every organizer page includes:

```php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../signin.php");
    exit();
}
```

### Session Management

- User ID tracking
- Role verification
- First name storage
- Email validation

### API Security

- Session-based authentication for AI API
- Input sanitization
- SQL injection prevention with prepared statements
- XSS protection with HTML escaping

### Password Security

- Bcrypt hashing
- Minimum password requirements
- Password confirmation

## AI Recommendation System

### How It Works

1. **Natural Language Processing**

   - Parses user messages
   - Extracts event requirements
   - Identifies keywords and patterns

2. **Multi-Criteria Scoring**

   ```
   Score = Capacity_Match (40%) + Budget_Match (40%) + Location (20%)
   ```

3. **Venue Ranking**
   - Calculates suitability scores
   - Orders by best match
   - Returns top 5 venues

### Supported Event Types

- Wedding
- Corporate (conference, seminar, meeting)
- Birthday
- Concert (music, show, performance)

### Parsed Requirements

- **Guests**: Number of attendees
- **Budget**: Event budget in PHP
- **Amenities**:
  - Parking
  - Catering
  - Sound system
  - Stage
  - Air conditioning
  - WiFi

### Example Queries

**Query 1**: "I'm planning a wedding for 150 guests with 120000 budget"

- Event Type: Wedding
- Guests: 150
- Budget: ₱120,000

**Query 2**: "Need a corporate venue for 200 people with parking and sound"

- Event Type: Corporate
- Guests: 200
- Amenities: Parking, Sound

**Query 3**: "Birthday party 80 pax, budget 75k, need catering"

- Event Type: Birthday
- Guests: 80
- Budget: ₱75,000
- Amenities: Catering

## UI/UX Highlights

### Color Scheme

- Primary: Purple (#9333EA)
- Secondary: Pink (#EC4899)
- Accent: Indigo (#4F46E5)

### AI Chatbot Banner

- Gradient background (purple to pink)
- Robot icon
- Feature badges
- Prominent "Start Chat" button
- Eye-catching design

### Chat Interface

- Modal dialog
- Gradient header
- Scrollable message area
- User messages (right, purple)
- Bot messages (left, white)
- Typing indicator
- Venue cards with details

### Responsive Design

- Mobile-friendly
- Touch-optimized
- Adaptive layouts
- Collapsible navigation

## API Endpoints

### AI Recommendation API

**Endpoint**: `/src/services/ai-recommendation.php`

**Method**: POST

**Authentication**: Session-based (organizer role required)

**Request Body**:

```json
{
  "message": "Wedding for 150 guests with 120000 budget",
  "context": "venue_recommendation"
}
```

**Response**:

```json
{
  "success": true,
  "response": "Great! I understand you're planning a Wedding event for 150 guests for ₱120,000 budget. Here are my top 3 venue recommendations...",
  "venues": [
    {
      "id": 1,
      "name": "Grand Pavilion",
      "capacity": 200,
      "price": 98000,
      "location": "Taguig City",
      "description": "Elegant indoor venue...",
      "score": 92
    }
  ],
  "parsed_data": {
    "event_type": "wedding",
    "guests": 150,
    "budget": 120000,
    "amenities": []
  }
}
```

## Troubleshooting

### Issue: "Unauthorized" error

**Solution**:

- Ensure logged in as organizer
- Check session is active
- Verify role in database

### Issue: Chatbot not responding

**Solution**:

- Check browser console for errors
- Verify API endpoint path
- Ensure database connection is active
- Check PHP error logs

### Issue: No venue recommendations

**Solution**:

- Ensure venues table has data
- Check venue availability_status = 'available'
- Verify venue pricing and capacity data
- Try broader search criteria

### Issue: Page not loading

**Solution**:

- Check file paths are correct
- Verify database connection in dbconnect.php
- Ensure session_start() is called
- Check PHP error logs

## Future Enhancements

### Planned Features

- [ ] Advanced filtering options
- [ ] Save favorite venues
- [ ] Event calendar view
- [ ] Budget calculator
- [ ] Venue comparison tool
- [ ] Contract management
- [ ] In-app messaging with venue managers
- [ ] Payment integration
- [ ] Event timeline planner
- [ ] Guest list management

### AI Improvements

- [ ] Machine learning model training
- [ ] Collaborative filtering
- [ ] Historical booking analysis
- [ ] Price optimization suggestions
- [ ] Alternative venue suggestions
- [ ] Weather-based recommendations

## Support

For issues or questions:

1. Check this documentation
2. Review error logs
3. Test with sample data
4. Verify database schema
5. Check file permissions

## Testing Checklist

- [ ] Organizer role added to database
- [ ] Test account created
- [ ] Login as organizer works
- [ ] Dashboard loads correctly
- [ ] Statistics display properly
- [ ] AI chatbot opens
- [ ] Chat messages send/receive
- [ ] Venue recommendations appear
- [ ] Quick actions work
- [ ] Recent events display
- [ ] Profile dropdown functions
- [ ] Sign out works

## Security Checklist

- [ ] Session validation on all pages
- [ ] Role-based access control
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] Password hashing
- [ ] Input sanitization
- [ ] CSRF protection (recommended)
- [ ] HTTPS enabled (production)
- [ ] Error messages don't expose system info
- [ ] API endpoints secured

## Performance Tips

1. **Database Optimization**

   - Index frequently queried columns
   - Use prepared statements
   - Limit result sets

2. **Caching**

   - Cache venue data
   - Session-based user data
   - Query result caching

3. **Frontend Optimization**
   - Minify CSS/JS
   - Lazy load images
   - Debounce chat input
   - Optimize chart rendering

## Conclusion

The Organizer Dashboard provides a secure, user-friendly interface for event planning with AI-powered venue recommendations. The prominent chatbot feature helps organizers quickly find suitable venues based on their specific requirements.
