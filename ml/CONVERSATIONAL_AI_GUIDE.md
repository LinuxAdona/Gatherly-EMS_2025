# 🤖 Conversational AI Event Planner - User Guide

## What's New?

Your AI Event Planning Assistant now uses an **intelligent conversational approach** that:

- ✅ Asks questions **incrementally** to understand your needs
- ✅ Recommends **both venues AND suppliers**
- ✅ Provides personalized recommendations for:
  - 🏛️ Venues
  - 🍽️ Catering Services
  - 🎵 Lights and Sound Systems
  - 📸 Photography Services
  - 🎥 Videography Services
  - 🎤 Hosts/Emcees
  - 💐 Styling and Flowers
  - 🪑 Equipment Rental

---

## How It Works

### Step 1: Start the Conversation

The AI will greet you and ask about your event type.

**Example:**

```
You: "I'm planning a wedding"
AI: "Got it! A wedding event. For your wedding, how many guests are you expecting?"
```

### Step 2: Answer Questions

The AI will ask you key questions in order:

1. **Event Type** (wedding, corporate, birthday, concert)
2. **Guest Count** (how many people)
3. **Budget** (your total budget)
4. **Date** (when is the event)
5. **Services Needed** (which suppliers you need)

### Step 3: Get Recommendations

Once all information is gathered, you'll receive:

- 🏛️ **Top 3 Venue Matches** with scores
- 👥 **Best 2 Suppliers per Category** that fit your budget

---

## Sample Conversations

### Quick Start (All Info at Once)

```
You: "Wedding for 150 guests with 100000 budget in March.
      I need catering, photography, styling, and lights and sounds"

AI: [Asks follow-up questions]
    [Then provides complete recommendations]
```

### Step-by-Step Approach

```
You: "I'm planning a wedding"
AI: "Perfect! For your wedding, how many guests are you expecting?"

You: "150 guests"
AI: "Excellent! What's your total budget for the event?"

You: "100000"
AI: "When are you planning to hold this event?"

You: "March 2026"
AI: "Now let's talk about services! Which would you like me to recommend?"

You: "all"
AI: [Provides complete venue and supplier recommendations]
```

### Just Venues (No Suppliers)

```
You: "Show me venues for a corporate event with 200 people"
AI: [Asks remaining questions]

You: "Just the venue for now"
AI: [Shows only venue recommendations]
```

---

## Service Categories Available

| Category                 | What's Included                                             |
| ------------------------ | ----------------------------------------------------------- |
| 🍽️ **Catering**          | Buffet packages, cocktail receptions, full-service catering |
| 🎵 **Lights & Sounds**   | Audio systems, lighting, DJ setups, concert-grade equipment |
| 📸 **Photography**       | Full-day coverage, engagement shoots, combo packages        |
| 🎥 **Videography**       | Cinematic videos, highlights, live streaming                |
| 🎤 **Host/Emcee**        | Wedding hosts, corporate emcees, party entertainers         |
| 💐 **Styling & Flowers** | Floral arrangements, event styling, theme decorations       |
| 🪑 **Equipment Rental**  | Tables, chairs, tents, stages, photo booths                 |

---

## How Recommendations Work

### Venue Scoring (0-100%)

The AI calculates scores based on:

- **Capacity Match** (30%) - How well the venue fits your guest count
- **Budget Match** (35%) - How affordable it is
- **Location** (15%) - Geographic preferences
- **Amenities** (20%) - Available facilities

### Supplier Selection

Suppliers are filtered by:

- **Budget Allocation** - Smart budget split across categories
- **Price Range** - Within your budget with flexibility
- **Availability** - Only available suppliers shown
- **Rating** - Best-rated suppliers prioritized

---

## Budget Allocation Guide

The AI automatically allocates your budget across services:

| Service           | Budget % | Example (₱100,000) |
| ----------------- | -------- | ------------------ |
| Venue             | 40%      | ₱40,000            |
| Catering          | 25%      | ₱25,000            |
| Styling & Flowers | 15%      | ₱15,000            |
| Lights & Sounds   | 8%       | ₱8,000             |
| Photography       | 6%       | ₱6,000             |
| Videography       | 4%       | ₱4,000             |
| Equipment Rental  | 2%       | ₱2,000             |

_These percentages are flexible and the AI will show options within ±30% of the allocation._

---

## Tips for Best Results

### ✅ DO:

- Provide specific numbers ("150 guests" not "many people")
- Mention your budget early
- Be clear about which services you need
- Use natural language - the AI understands context

### ❌ DON'T:

- Give vague information ("some guests", "not sure about budget")
- Skip questions - answer each one for better results
- Expect exact prices - recommendations are starting estimates

---

## Example Queries That Work Great

### Wedding Planning

```
"I'm planning a garden wedding for 120 guests with a budget of 80000.
 I need catering, photography, and floral styling."
```

### Corporate Event

```
"Corporate conference for 200 people in June. Budget is 150000.
 Need venue, sound system, and catering."
```

### Birthday Party

```
"18th birthday party for 80 guests, budget around 50000.
 Need venue, catering, host, and photo booth."
```

### Concert/Show

```
"Music concert for 300 people with 200000 budget.
 Need large venue with concert-grade sound and lighting."
```

---

## Understanding Your Results

### Venue Cards Show:

- Venue name and location
- Capacity (guest limit)
- Base price
- Brief description
- Amenities available
- **Match Score** (how well it fits your needs)

### Supplier Cards Show:

- Service name
- Supplier/company name
- Service description
- Price
- Location
- Contact information

---

## What's Next?

After receiving recommendations:

1. **Review** venues and suppliers
2. **Click "View Details"** on venues for more info
3. **Contact suppliers** directly using provided info
4. **Book** your favorites
5. **Track** everything in your dashboard

---

## Need Help?

### Common Issues

**"AI keeps asking the same question"**

- Make sure you're answering with numbers/specifics
- Example: Say "150" not "around 100-200"

**"No suppliers shown for a category"**

- Budget might be too low for that service
- Try increasing budget or selecting fewer services

**"Venues don't match my needs"**

- Be more specific about guest count
- Mention special requirements (outdoor, AC, parking)

**"Want to start over"**

- Close and reopen the chat modal
- The conversation will reset

---

## Technical Details

### How the AI Works:

1. **NLP Parsing** - Extracts key information from your messages
2. **Stage Detection** - Knows what question to ask next
3. **ML Scoring** - Uses machine learning to score venues
4. **Budget Optimization** - Intelligently allocates budget across services
5. **Multi-Criteria Matching** - Considers capacity, budget, location, amenities

### Powered By:

- Python 3.14
- scikit-learn (Machine Learning)
- MySQL Database
- Natural Language Processing
- Multi-Criteria Decision Making (MCDM)

---

## Privacy & Data

- ✅ Conversations are not stored permanently
- ✅ Your data is only used for recommendations
- ✅ Contact information is shown only for your selected suppliers
- ✅ Budget information is kept confidential

---

## Updates & Improvements

### Current Version: 2.0 (November 2025)

- ✅ Conversational approach with incremental questions
- ✅ Venue + Supplier recommendations
- ✅ Budget-aware suggestions
- ✅ 7 service categories supported
- ✅ Match scoring with ML

### Coming Soon:

- Calendar integration for date checking
- Supplier ratings and reviews
- Package deals (venue + services bundled)
- Comparison tool for venues/suppliers
- Save favorites feature
- Direct booking capability

---

## Feedback

Enjoying the AI Event Planner? Have suggestions?

- Use the feedback button in your dashboard
- Or email support (coming soon)

**Happy Event Planning! 🎉**
