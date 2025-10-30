# Gatherly EMS - AI-Powered Event Management System

## 🚀 Features Implemented

### 1. **AI-Powered Venue Recommendations**

- Multi-criteria decision-making algorithm
- Weighted scoring system (capacity, price, location, amenities, availability)
- Collaborative filtering based on similar events
- Match score calculation (0-100%)
- Personalized recommendations

### 2. **Dynamic Pricing & Forecasting**

- Seasonal pricing (peak vs off-peak)
- Day-based pricing (weekend vs weekday)
- Demand-based multipliers
- Real-time price calculations
- Occupancy rate tracking
- Revenue optimization suggestions
- Time-series forecasting for venue owners

### 3. **Smart Resource Optimization**

- Automatic conflict detection
- Alternative venue suggestions
- Real-time availability checking
- Booking history analysis

### 4. **Location Intelligence**

- Google Maps API integration
- Distance calculation (Haversine formula)
- Travel time estimation
- Accessibility scoring
- Location-based recommendations

### 5. **Analytics Dashboard**

- Venue occupancy trends
- Revenue forecasting
- Event type distribution
- Booking patterns
- Average budget analysis
- Performance insights

### 6. **Communication System**

- In-app chat between venue managers and clients
- AI chatbot for venue recommendations
- Real-time messaging
- Attachment support

### 7. **Contract Generation**

- Auto-generate PDF contracts
- Customizable templates
- Event-specific terms
- Digital signatures

## 📁 Project Structure

```
Gatherly-EMS_2025/
├── public/
│   ├── assets/
│   │   ├── images/
│   │   ├── js/
│   │   └── css/
│   └── pages/
│       ├── home.php
│       ├── signin.php
│       ├── signup.php
│       ├── dashboard.php
│       ├── venue/
│       │   ├── search.php          # AI-powered venue search
│       │   ├── venue-details.php   # Detailed venue information
│       │   └── booking.php         # Booking interface
│       └── chat/
│           ├── ai-chat.php         # AI chatbot interface
│           └── venue-chat.php      # Manager-client chat
├── src/
│   ├── models/
│   │   ├── RecommendationEngine.php   # AI recommendation system
│   │   ├── DynamicPricingEngine.php   # Dynamic pricing algorithm
│   │   ├── ChatbotEngine.php          # AI chatbot
│   │   └── ContractGenerator.php      # PDF contract generation
│   ├── controllers/
│   │   ├── VenueController.php
│   │   ├── BookingController.php
│   │   └── ChatController.php
│   ├── services/
│   │   ├── dbconnect.php
│   │   ├── signin-handler.php
│   │   └── signup-handler.php
│   ├── api/
│   │   ├── venues.php
│   │   ├── recommendations.php
│   │   ├── pricing.php
│   │   └── chat.php
│   ├── utils/
│   │   ├── helpers.php
│   │   └── validators.php
│   ├── db/
│   │   ├── sad_db.sql
│   │   └── enhanced_schema.sql
│   └── components/
│       └── Footer.php
└── README.md
```

## 🛠️ Installation & Setup

### Prerequisites

- XAMPP (Apache + MySQL + PHP 7.4+)
- Composer (optional, for future dependencies)
- Google Maps API Key
- Modern web browser

### Step 1: Database Setup

1. Start XAMPP and run Apache & MySQL
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create database:
   ```sql
   CREATE DATABASE sad_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```
4. Import the base schema:
   - Import `src/db/sad_db.sql`
5. Run the enhanced schema:
   - Import `src/db/enhanced_schema.sql`

### Step 2: Configure Google Maps API

1. Get API key from [Google Cloud Console](https://console.cloud.google.com/)
2. Enable these APIs:
   - Maps JavaScript API
   - Places API
   - Geocoding API
   - Distance Matrix API
3. Update API key in `public/pages/venue/search.php`:
   ```javascript
   <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
   ```

### Step 3: Database Configuration

Update `src/services/dbconnect.php` if needed:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "sad_db";
```

### Step 4: File Permissions

Ensure write permissions for:

- `src/uploads/` (for contract PDFs)
- `src/logs/` (for error logs)

### Step 5: Access the Application

1. Navigate to: `http://localhost/Gatherly-EMS_2025/public/pages/home.php`
2. Sign up for an account or use existing credentials:
   - **Username**: client_dore
   - **Password**: password123
   - **Role**: Client

## 🎯 How to Use

### For Clients (Event Organizers)

#### 1. **Search for Venues**

```
Navigate to: venue/search.php
```

- Enter event details (type, date, guest count, budget)
- Add location preference (optional)
- Select required amenities
- Click "Find Perfect Venues"
- System returns top 3 recommendations with match scores

#### 2. **Understanding Match Scores**

- **90-100%**: Perfect match
- **80-89%**: Excellent match
- **70-79%**: Good match
- **60-69%**: Acceptable match
- **Below 60%**: Consider alternatives

#### 3. **View Dynamic Pricing**

Each venue shows:

- Base price
- Current price (with discounts/premiums)
- Pricing type (Peak Premium, Discounted, Standard)
- Season multiplier
- Demand multiplier

#### 4. **Book a Venue**

- Click "Book Now" on chosen venue
- Review pricing and contract
- Make payment
- System generates contract automatically

#### 5. **Use AI Chatbot**

```
Navigate to: chat/ai-chat.php
```

- Ask questions naturally:
  - "Find me a wedding venue for 200 guests under 150K"
  - "Which venues have both catering and parking?"
  - "Show me discounted venues for next month"

### For Venue Managers

#### 1. **Monitor Dashboard**

- View occupancy trends
- Track inquiries and bookings
- See revenue forecasts
- Analyze pricing effectiveness

#### 2. **Adjust Pricing Strategy**

The system auto-calculates prices but you can:

- Set custom peak/off-peak rates
- Define seasonal periods
- View pricing optimization suggestions

#### 3. **Chat with Clients**

- Respond to inquiries
- Share venue details
- Negotiate terms
- Confirm bookings

## 🧮 AI Recommendation Algorithm

### Scoring Criteria

**1. Capacity Score (Default Weight: 25%)**

- Perfect: Guests are 70-90% of capacity
- Score decreases if over-capacity or under-utilized

**2. Price Score (Default Weight: 30%)**

- Best: Price is 70-100% of budget
- Penalty for exceeding budget
- Considers dynamic pricing

**3. Location Score (Default Weight: 20%)**

- Based on distance from preferred location
- Uses Haversine formula for accuracy
- < 5km: 100%, 5-10km: 90%, etc.

**4. Amenities Score (Default Weight: 15%)**

- Matches required amenities
- Bonus for additional features

**5. Availability Score (Default Weight: 10%)**

- Real-time availability check
- Historical booking patterns

### Collaborative Filtering

The system boosts venues that:

- Hosted similar event types successfully
- Had high satisfaction scores (4.0+)
- Match guest count range (±20%)

### Formula

```
Suitability Score = Σ(Criterion Score × Weight) + Collaborative Boost
```

## 💰 Dynamic Pricing Logic

### Multipliers

**Season Multiplier:**

- Peak (Dec-Feb, Jun-Aug): 1.30x
- Shoulder (Mar-May, Sep-Nov): 1.00x
- Off-peak: 0.85x

**Day Type Multiplier:**

- Weekend: 1.20x
- Weekday: 0.95x

**Demand Multiplier:**

- High demand (10+ inquiries): 1.30x
- Moderate demand (5-9 inquiries): 1.15x
- Normal (2-4 inquiries): 1.00x
- Low demand (0-1 inquiries): 0.90x

### Price Calculation

```
Final Price = Base Price × Season × Day Type × Demand
```

### Optimization Strategy

- **Low occupancy (<40%)**: Offer 15% discount
- **High occupancy (>80%)**: Apply 10% premium
- **Moderate (40-80%)**: Maintain optimal price

## 📊 Analytics Features

### For Venue Owners

1. **Occupancy Forecasting**

   - 6-month prediction
   - Seasonal adjustments
   - Confidence levels

2. **Pricing Insights**

   - Average monthly prices
   - Demand patterns
   - Revenue optimization tips

3. **Booking Trends**
   - Event type distribution
   - Peak booking periods
   - Client satisfaction scores

### For Platform Admins

1. **System-wide Analytics**

   - Total bookings
   - Revenue tracking
   - Most popular venues
   - Average match scores

2. **AI Performance**
   - Recommendation accuracy
   - User satisfaction
   - Conversion rates

## 🔒 Security Features

- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- Session management
- Role-based access control

## 🚧 Future Enhancements

1. **Payment Integration**

   - PayPal/Stripe integration
   - Installment plans
   - Automatic refunds

2. **Advanced ML**

   - TensorFlow.js for predictions
   - Sentiment analysis on reviews
   - Image recognition for venue photos

3. **Mobile App**

   - React Native application
   - Push notifications
   - Offline mode

4. **Social Features**
   - Venue reviews and ratings
   - Photo galleries
   - Social media sharing

## 🐛 Troubleshooting

### Common Issues

**1. "Class not found" error**

```
Solution: Check file paths in require_once statements
Ensure files are in correct directories
```

**2. Database connection failed**

```
Solution: Verify MySQL is running
Check credentials in dbconnect.php
```

**3. Google Maps not loading**

```
Solution: Verify API key is valid
Check API quotas in Google Cloud Console
```

**4. No recommendations shown**

```
Solution: Ensure enhanced_schema.sql is imported
Check if venues table has location data (lat/lng)
```

## 📞 Support

For issues or questions:

1. Check the troubleshooting section
2. Review error logs in `src/logs/`
3. Verify database structure matches schema

## 📄 License

This project is developed for educational purposes.

## 👥 Credits

Developed by: [Your Team Name]
Institution: [Your Institution]
Year: 2025

---

**Note**: Remember to replace `YOUR_GOOGLE_MAPS_API_KEY` with your actual API key before deployment!
