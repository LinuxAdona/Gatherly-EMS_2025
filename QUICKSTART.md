# Quick Start Guide - Gatherly EMS

## 🚀 5-Minute Setup

### Step 1: Extract Files

1. Extract the project to `C:\xampp\htdocs\Gatherly-EMS_2025`

### Step 2: Database Setup

1. Start XAMPP → Start Apache and MySQL
2. Open browser → http://localhost/phpmyadmin
3. Create database:
   - Click "New" in left sidebar
   - Database name: `sad_db`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"
4. Import base schema:
   - Click on `sad_db` database
   - Go to "Import" tab
   - Choose file: `src/db/sad_db.sql`
   - Click "Go"
5. Import enhanced schema:
   - Go to "Import" tab again
   - Choose file: `src/db/enhanced_schema.sql`
   - Click "Go"

### Step 3: Access the Application

1. Open browser
2. Navigate to: `http://localhost/Gatherly-EMS_2025/public/pages/home.php`
3. Click "Sign up" to create an account OR use demo account:
   - **Email**: dore@gmail.com
   - **Password**: password123

### Step 4: Try the AI Features

#### Search for Venues with AI Recommendations:

1. After login, go to: Menu → Search Venues
2. OR directly: `http://localhost/Gatherly-EMS_2025/public/pages/venue/search.php`
3. Fill in:
   - Event Type: Birthday
   - Event Date: (pick any future date)
   - Guests: 150
   - Budget: 120000
   - Check: Catering, Sound System
4. Click "Find Perfect Venues"
5. You'll see:
   - Top 3 venues with match scores (%)
   - Breakdown of scores (capacity, price, location, amenities)
   - Dynamic pricing with discounts
   - Reasons why each venue matches

#### Chat with AI Assistant:

1. Go to: `http://localhost/Gatherly-EMS_2025/public/pages/chat/ai-chat.php`
2. Try these examples:
   - "Find me a wedding venue for 200 guests under 150K"
   - "Show me venues with parking and catering"
   - "Which venues are available next month?"
   - "Compare the top 3 venues"

#### View Analytics Dashboard:

1. Go to: `http://localhost/Gatherly-EMS_2025/public/pages/dashboard.php`
2. See:
   - Total bookings and revenue
   - Booking trends chart
   - Event type distribution
   - Venue occupancy rates
   - Top booked venues

## 🔧 Optional: Google Maps Integration

### Get API Key (Free):

1. Go to: https://console.cloud.google.com
2. Create new project: "Gatherly-EMS"
3. Enable these APIs:
   - Maps JavaScript API
   - Places API
   - Geocoding API
4. Create credentials → API Key
5. Copy your API key

### Add API Key:

1. Open: `public/pages/venue/search.php`
2. Find line 133:
   ```html
   <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
   ```
3. Replace `YOUR_GOOGLE_MAPS_API_KEY` with your actual key
4. Save file

Now you can search venues by location!

## 📝 Test Scenarios

### Scenario 1: Birthday Party

```
Event Type: Birthday
Guests: 150
Budget: 120,000
Amenities: Catering, Sound System
```

**Expected**: System recommends venues with 80%+ match score

### Scenario 2: Corporate Event

```
Event Type: Corporate
Guests: 200
Budget: 100,000
Amenities: Air Conditioning, Parking, Stage
```

**Expected**: Prioritizes venues with professional setup

### Scenario 3: Budget-Conscious Wedding

```
Event Type: Wedding
Guests: 250
Budget: 90,000
Date: (weekday in off-peak month)
```

**Expected**: Shows discounted prices (15-20% off)

### Scenario 4: Peak Season Premium

```
Event Type: Any
Date: December 25 or June 15 (weekend)
```

**Expected**: Prices 30-50% higher (peak + weekend)

## 🎯 Key Features to Explore

### 1. Match Score Breakdown

- Green (90-100%): Perfect match
- Light Green (80-89%): Excellent
- Yellow (70-79%): Good
- Orange (60-69%): Acceptable

### 2. Dynamic Pricing Indicators

- 🟢 "Discounted": Save 10-20%
- 🟡 "Standard": Regular price
- 🔴 "Peak Premium": High demand
- 🔵 "High Demand": Multiple inquiries

### 3. Venue Recommendations

Each recommendation shows:

- Overall match percentage
- Individual scores (capacity, price, location, amenities, availability)
- Reason why it's recommended
- Current dynamic price
- Available amenities

### 4. AI Chatbot Understanding

The chatbot understands:

- **Event details**: "wedding for 200 guests"
- **Budget**: "under 150K" or "₱120,000"
- **Location**: "in Makati" or "near BGC"
- **Amenities**: "with parking and catering"
- **Combined**: "Find a corporate event venue for 150 people, budget 100K, needs sound system and AC"

## ⚠️ Troubleshooting

### Error: "Database connection failed"

**Solution**:

- Make sure MySQL is running in XAMPP
- Check `src/services/dbconnect.php` has correct credentials

### Error: "Class not found"

**Solution**:

- Verify all files are in correct folders
- Check file paths in `require_once` statements

### No venues showing in search

**Solution**:

- Make sure `enhanced_schema.sql` was imported
- Check if venues table has data with lat/lng coordinates
- Run this SQL:
  ```sql
  SELECT COUNT(*) FROM venues WHERE latitude IS NOT NULL;
  ```
  Should return 4

### Dynamic pricing shows only base price

**Solution**:

- Import `enhanced_schema.sql` to add pricing tables
- System needs pricing_history and venue_demand_log tables

### AI Chatbot not responding

**Solution**:

- Check PHP error logs in XAMPP
- Verify `ai_chat_messages` table exists
- Test database connection

## 📊 Sample Queries for Testing

Run these in phpMyAdmin to verify data:

```sql
-- Check venues with enhanced data
SELECT venue_id, venue_name, capacity, base_price,
       parking_capacity, has_stage_setup,
       catering_available, sound_system_available,
       latitude, longitude
FROM venues;

-- Check booking history for collaborative filtering
SELECT * FROM venue_bookings_history;

-- Check if recommendations are being saved
SELECT * FROM recommendations ORDER BY recommendation_id DESC LIMIT 5;

-- View demand tracking
SELECT * FROM venue_demand_log;

-- See pricing history
SELECT * FROM pricing_history ORDER BY created_at DESC LIMIT 10;
```

## 🎓 Learning the System

### For Developers:

1. **RecommendationEngine.php**: Study the multi-criteria scoring algorithm
2. **DynamicPricingEngine.php**: See how seasonal/demand pricing works
3. **ChatbotEngine.php**: Learn natural language processing basics
4. **search.php**: See how recommendations are displayed

### For Users:

1. Start with AI chatbot (easiest)
2. Try advanced search (more control)
3. Explore dashboard (see patterns)
4. Test different scenarios

## 🔐 Default Accounts

### Administrator

- Email: admin@example.com
- Password: password123

### Coordinator

- Email: linux@gmail.com
- Password: password123

### Client

- Email: dore@gmail.com
- Password: password123

## 📞 Need Help?

1. Check README.md for detailed documentation
2. Review troubleshooting section above
3. Check XAMPP error logs: `xampp/apache/logs/error.log`
4. Verify all database tables exist

## ✨ Next Steps

After setup:

1. ✅ Test venue search with different criteria
2. ✅ Chat with AI assistant
3. ✅ View analytics dashboard
4. ✅ Try booking a venue
5. ✅ Test dynamic pricing on different dates
6. ✅ Compare venue recommendations

**Enjoy exploring Gatherly EMS! 🎉**
