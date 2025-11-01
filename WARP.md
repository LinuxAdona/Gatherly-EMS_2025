# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

Project overview
- Stack: PHP (procedural + light MVC), MySQL, Tailwind CSS (v4 via CLI), vanilla JS.
- Serve via Apache/XAMPP. App entry points are under public/pages/ (e.g., public/pages/home.php).
- Database schemas: src/db/sad_db.sql (base), src/db/enhanced_schema.sql (features used by engines).

Common commands
- Node deps (for Tailwind only):
  - npm ci
- Build Tailwind CSS (one-off):
  - npx @tailwindcss/cli -i src/input.css -o src/output.css --minify
- Watch Tailwind during development:
  - npx @tailwindcss/cli -i src/input.css -o src/output.css --watch
- Import database (MySQL CLI, from repo root):
  - mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sad_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
  - mysql -u root -p sad_db < src/db/sad_db.sql
  - mysql -u root -p sad_db < src/db/enhanced_schema.sql
- Run app locally:
  - Start Apache and MySQL in XAMPP, then open http://localhost/Gatherly-EMS_2025/public/pages/home.php

Notes on tooling
- No PHP test suite or linter configured (no composer.json, phpunit.xml, or PHP-CS-Fixer). There are no npm lint/test scripts.
- Tailwind config globs (tailwind.config.js) include PHP under src/** and public/**; update globs if you add new view paths.

Big-picture architecture
- Views (UI): public/pages/**. Key pages:
  - home.php (landing), signin.php, signup.php, dashboard.php, venue/search.php, chat/ai-chat.php
  - Most pages link to compiled CSS at src/output.css; some (e.g., dashboard.php) load Tailwind via CDN.
- Models (domain logic): src/models/**
  - RecommendationEngine.php: multi-criteria venue scoring with adjustable weights; produces top-N recommendations and per-criterion breakdown.
  - DynamicPricingEngine.php: season/day/demand multipliers, price calculation, inquiry tracking (venue_demand_log), and pricing_history integration.
  - ChatbotEngine.php: intent/entity parsing for natural-language queries; delegates to RecommendationEngine and formats responses.
- Services: src/services/**
  - dbconnect.php centralizes MySQL connection; signin-/signup-/signout-handlers implement auth flows used by public pages.
- Data layer: src/db/** SQL files create/extend tables used by engines and analytics (venues, events, recommendations, pricing_history, venue_demand_log, ai_chat_messages, etc.).
- Components: src/components/Footer.php shared UI footer.
- Assets: public/assets/** (images, js); Tailwind source at src/input.css compiled to src/output.css.

Core request flows
- Venue search (public/pages/venue/search.php):
  1) Validates session → builds criteria from POST → RecommendationEngine::getRecommendations(criteria, topN, optional custom weights)
  2) For each recommended venue, DynamicPricingEngine::calculatePrice(venueId, eventDate) augments pricing data
  3) Renders top results with score breakdown and dynamic price indicators
- AI chat (public/pages/chat/ai-chat.php):
  1) ChatbotEngine extracts intent/entities → calls RecommendationEngine and formats conversational replies
- Analytics (public/pages/dashboard.php):
  - Aggregates metrics via SQL (bookings, revenue, average suitability, trends) and renders with Chart.js

Configuration hotspots
- Database credentials: src/services/dbconnect.php
- Google Maps API key (Places/Geocoding/Distance Matrix): update script tag in public/pages/venue/search.php
- Recommendation weights and pricing multipliers live inside the respective engine classes; adjust there if changing defaults.
