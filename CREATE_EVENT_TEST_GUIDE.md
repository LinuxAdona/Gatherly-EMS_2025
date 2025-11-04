# Create Event Feature - Quick Test Guide

## Pre-Test Checklist

- [ ] XAMPP MySQL/MariaDB is running
- [ ] Database `sad_db` exists with all tables
- [ ] Logged in as an organizer user
- [ ] Browser console open (F12) for debugging

## Test Cases

### Test 1: Page Load ✅

**Steps**:

1. Navigate to `http://localhost/Gatherly-EMS_2025/public/pages/organizer/create-event.php`
2. Verify page loads without errors

**Expected Results**:

- ✅ Page displays properly
- ✅ Navigation bar shows
- ✅ Form sections visible
- ✅ Venue cards displayed
- ✅ Service sections displayed
- ✅ Cost summary shows ₱0.00

---

### Test 2: Venue Selection ✅

**Steps**:

1. Click on any venue card
2. Observe visual feedback

**Expected Results**:

- ✅ Card border turns indigo
- ✅ Card background changes to light indigo
- ✅ Radio button gets checked
- ✅ Cost summary updates with venue price
- ✅ Previously selected venue deselects

---

### Test 3: Service Selection ✅

**Steps**:

1. Check several service checkboxes from different categories
2. Observe cost updates

**Expected Results**:

- ✅ Checkbox gets checked/unchecked
- ✅ Services cost updates in sidebar
- ✅ Total cost updates correctly
- ✅ Multiple services can be selected
- ✅ Cost calculation is accurate

---

### Test 4: Cost Calculation ✅

**Steps**:

1. Select venue worth ₱40,000
2. Select services:
   - Basic Sound Package: ₱12,000
   - Premium Photography: ₱25,000
3. Check cost summary

**Expected Results**:

- ✅ Venue cost: ₱40,000.00
- ✅ Services cost: ₱37,000.00
- ✅ Total cost: ₱77,000.00

---

### Test 5: Form Validation - Empty Fields ❌

**Steps**:

1. Click "Create Event" without filling any fields
2. Observe error message

**Expected Results**:

- ✅ Red alert appears at top
- ✅ Message: "Please enter an event name"
- ✅ Form doesn't submit

---

### Test 6: Form Validation - Past Date ❌

**Steps**:

1. Fill event name: "Test Event"
2. Select event type: "Wedding"
3. Enter guests: 100
4. Manually set date to past (if possible)
5. Select a venue
6. Click "Create Event"

**Expected Results**:

- ✅ Red alert appears
- ✅ Message: "Event date must be in the future"
- ✅ Form doesn't submit

---

### Test 7: Form Validation - Capacity Warning ⚠️

**Steps**:

1. Fill event name: "Large Event"
2. Select event type: "Corporate"
3. Enter guests: 500 (more than any venue capacity)
4. Select date in future
5. Select smallest venue
6. Click "Create Event"

**Expected Results**:

- ✅ Yellow warning alert appears
- ✅ Message mentions capacity mismatch
- ✅ Asks for confirmation
- ✅ Can proceed or cancel

---

### Test 8: Successful Event Creation ✅

**Steps**:

1. Fill all required fields:
   - Event name: "Test Wedding"
   - Event type: "Wedding"
   - Theme: "Rustic Garden"
   - Expected guests: 150
   - Event date: [Select future date]
2. Select venue: "Aurora Pavilion"
3. Select services: Check 2-3 services
4. Click "Create Event"
5. Wait for response

**Expected Results**:

- ✅ Button shows "Creating Event..." with spinner
- ✅ Green success alert appears
- ✅ Message: "Event created successfully! Redirecting..."
- ✅ Redirects to dashboard after 2 seconds
- ✅ New event appears in dashboard
- ✅ Database record created in `events` table
- ✅ Service records created in `event_services` table

---

### Test 9: Minimum Fields ✅

**Steps**:

1. Fill only required fields:
   - Event name: "Minimal Event"
   - Event type: "Birthday"
   - Expected guests: 50
   - Event date: [Future date]
2. Select venue
3. Don't select any services
4. Don't fill theme
5. Submit

**Expected Results**:

- ✅ Form submits successfully
- ✅ Event created with null theme
- ✅ Event created with no services
- ✅ Total cost = venue cost only

---

### Test 10: Session Security 🔒

**Steps**:

1. Log out
2. Try to access create-event.php directly via URL
3. Try to POST to create-event-handler.php

**Expected Results**:

- ✅ Redirects to signin page
- ✅ Returns 403 Unauthorized error
- ✅ Cannot create event without login

---

### Test 11: Database Verification 💾

**Steps**:

1. After creating an event, open phpMyAdmin
2. Check `events` table
3. Check `event_services` table

**SQL Query**:

```sql
-- View latest event
SELECT * FROM events ORDER BY created_at DESC LIMIT 1;

-- View event services
SELECT es.*, s.service_name
FROM event_services es
JOIN services s ON es.service_id = s.service_id
WHERE es.event_id = [YOUR_EVENT_ID];
```

**Expected Results**:

- ✅ New record in `events` table
- ✅ All fields populated correctly
- ✅ `status` = 'pending'
- ✅ `client_id` matches logged-in user
- ✅ Records in `event_services` for selected services
- ✅ `event_id` matches across tables

---

### Test 12: UI Responsiveness 📱

**Steps**:

1. Open browser DevTools
2. Toggle device toolbar (Ctrl+Shift+M)
3. Test different screen sizes:
   - Mobile (375px)
   - Tablet (768px)
   - Desktop (1920px)

**Expected Results**:

- ✅ Layout adapts to screen size
- ✅ No horizontal scrolling
- ✅ Buttons remain clickable
- ✅ Text remains readable
- ✅ Cards stack properly on mobile

---

## Common Issues & Quick Fixes

### Issue: No venues showing

**Fix**:

```sql
-- Check if venues exist
SELECT * FROM venues WHERE availability_status = 'available';

-- If empty, insert sample venue
INSERT INTO venues (venue_name, capacity, base_price, location, availability_status)
VALUES ('Sample Venue', 150, 40000, 'Makati City', 'available');
```

### Issue: No services showing

**Fix**:

```sql
-- Check if services exist
SELECT * FROM services;

-- Check if suppliers exist
SELECT * FROM suppliers WHERE availability_status = 'available';
```

### Issue: "Unauthorized" error

**Fix**:

- Clear browser cookies
- Log in again
- Verify session is active: `print_r($_SESSION);`

### Issue: Cost not calculating

**Fix**:

- Open browser console (F12)
- Check for JavaScript errors
- Verify data attributes on elements:
  - `data-venue-price`
  - `data-price`

### Issue: Form submission fails

**Fix**:

- Check PHP error log: `C:\xampp\php\logs\php_error_log`
- Verify database connection
- Check network tab in DevTools for response

---

## Browser Console Commands

### Check selected venue

```javascript
document.querySelector('input[name="venue_id"]:checked');
```

### Check selected services

```javascript
document.querySelectorAll(".service-checkbox:checked");
```

### Manually trigger cost update

```javascript
updateCostSummary();
```

### Check form data

```javascript
new FormData(document.getElementById("createEventForm"));
```

---

## Success Criteria

All tests should pass with these results:

- ✅ Page loads without errors
- ✅ All UI elements interactive
- ✅ Real-time cost calculation working
- ✅ All validations working
- ✅ Form submits successfully
- ✅ Database records created correctly
- ✅ Redirects after success
- ✅ Security checks in place
- ✅ Responsive on all devices

---

**Test Date**: ******\_\_\_******
**Tester**: ******\_\_\_******
**Result**: ⬜ Pass ⬜ Fail
**Notes**: **********************\_\_\_**********************
