-- Verification Queries
-- Run these after importing revenue_data_update.sql to verify the data

-- 1. Check total number of completed events
SELECT COUNT(*) as total_events 
FROM events 
WHERE status = 'completed';
-- Expected: Should be around 70 events (10 original + 60 new)

-- 2. Check events by year
SELECT 
    YEAR(event_date) as year,
    COUNT(*) as event_count,
    SUM(total_cost) as total_revenue
FROM events 
WHERE status = 'completed'
GROUP BY YEAR(event_date)
ORDER BY year DESC;
-- Expected: Should show years 2025, 2024, 2023, 2022, 2021, 2020

-- 3. Check revenue for 2023
SELECT 
    MONTHNAME(event_date) as month,
    COUNT(*) as events,
    SUM(total_cost) as revenue
FROM events 
WHERE status = 'completed' 
    AND YEAR(event_date) = 2023
GROUP BY MONTH(event_date), MONTHNAME(event_date)
ORDER BY MONTH(event_date);
-- Expected: Should show 12 events across different months in 2023

-- 4. Check specific month (April 2023)
SELECT 
    DAY(event_date) as day,
    event_name,
    total_cost as revenue
FROM events 
WHERE status = 'completed' 
    AND YEAR(event_date) = 2023 
    AND MONTH(event_date) = 4
ORDER BY day;
-- Expected: Should show event(s) in April 2023

-- 5. Check July 2020 specifically
SELECT 
    DAY(event_date) as day,
    event_name,
    event_type,
    total_cost as revenue
FROM events 
WHERE status = 'completed' 
    AND YEAR(event_date) = 2020 
    AND MONTH(event_date) = 7
ORDER BY day;
-- Expected: Should show event(s) in July 2020

-- 6. Total revenue by year (summary)
SELECT 
    YEAR(event_date) as year,
    CONCAT('₱', FORMAT(SUM(total_cost), 2)) as total_revenue
FROM events 
WHERE status = 'completed'
GROUP BY YEAR(event_date)
ORDER BY year DESC;
-- Expected: Shows formatted revenue for each year

-- 7. Check for any issues (events without dates or revenue)
SELECT 
    event_id,
    event_name,
    event_date,
    total_cost,
    status
FROM events 
WHERE event_date IS NULL 
    OR total_cost IS NULL 
    OR total_cost = 0;
-- Expected: Should return 0 rows

-- 8. Monthly average revenue
SELECT 
    YEAR(event_date) as year,
    CONCAT('₱', FORMAT(AVG(total_cost), 2)) as avg_revenue_per_event
FROM events 
WHERE status = 'completed'
GROUP BY YEAR(event_date)
ORDER BY year DESC;
-- Expected: Shows average event revenue per year
