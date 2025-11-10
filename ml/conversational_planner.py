#!/usr/bin/env python3
"""
Conversational AI Event Planning Assistant
Uses incremental questioning to gather requirements and recommends venues + suppliers
Enhanced with Naive Bayes classifier for intelligent venue matching
"""

import sys
import json
import re
import mysql.connector
import numpy as np
from sklearn.naive_bayes import GaussianNB
from sklearn.preprocessing import StandardScaler
from typing import Dict, List, Tuple, Any, Optional

class ConversationalEventPlanner:
    def __init__(self):
        self.db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'sad_db'
        }
        
        # Define conversation stages
        self.stages = [
            'greeting',
            'event_type',
            'guest_count',
            'budget',
            'date',
            'services_needed',
            'recommendations'
        ]
        
        # Service categories
        self.service_categories = [
            'Catering',
            'Lights and Sounds',
            'Photography',
            'Videography',
            'Host/Emcee',
            'Styling and Flowers',
            'Equipment Rental'
        ]
    
    def determine_stage(self, conversation_state: Dict[str, Any], user_message: str) -> Tuple[str, Dict[str, Any]]:
        """
        Determine what stage we're at in the conversation
        Returns: (stage, extracted_data)
        """
        message_lower = user_message.lower()
        
        # Check if user is providing specific information
        data: Dict[str, Any] = {}
        
        # Parse event type
        event_types = {
            'wedding': ['wedding', 'marriage', 'nuptial', 'bride', 'groom'],
            'corporate': ['corporate', 'business', 'conference', 'seminar', 'meeting', 'company'],
            'birthday': ['birthday', 'party', 'celebration', 'debut', '18th', 'bday'],
            'concert': ['concert', 'music', 'show', 'performance', 'festival']
        }
        
        for event_type, keywords in event_types.items():
            if any(keyword in message_lower for keyword in keywords):
                data['event_type'] = event_type
                break
        
        # Parse guest count
        guest_patterns = [
            r'(\d+)\s*(?:guests?|people|attendees?|pax|persons?)',
            r'(?:for|about|around|approximately)\s*(\d+)',
        ]
        for pattern in guest_patterns:
            match = re.search(pattern, message_lower)
            if match:
                data['guests'] = int(match.group(1))
                break
        
        # Parse budget
        budget_patterns = [
            r'(?:₱|php|peso|pesos?)\s*([\d,]+)',
            r'([\d,]+)\s*(?:₱|php|peso|pesos?|budget)',
            r'budget\s*(?:of|is|:)?\s*([\d,]+)',
        ]
        for pattern in budget_patterns:
            match = re.search(pattern, message_lower)
            if match:
                data['budget'] = int(match.group(1).replace(',', ''))
                break
        
        # If at budget stage and message is just a number, treat it as budget
        if not data.get('budget') and conversation_state.get('event_type') and conversation_state.get('guests'):
            # Try to extract any standalone number (likely a budget response)
            standalone_number = re.search(r'^[\s]*(\d[\d,]*)\s*$', message_lower)
            if standalone_number:
                data['budget'] = int(standalone_number.group(1).replace(',', ''))
        
        # Parse date
        date_patterns = [
            r'(\d{4})-(\d{1,2})-(\d{1,2})',
            r'(\d{1,2})/(\d{1,2})/(\d{4})',
            r'(january|february|march|april|may|june|july|august|september|october|november|december)\s+(\d{1,2})',
        ]
        for pattern in date_patterns:
            match = re.search(pattern, message_lower)
            if match:
                data['date_mentioned'] = True
                break
        
        # Parse services needed
        service_keywords = {
            'catering': ['catering', 'food', 'buffet', 'meal', 'cuisine', 'chef'],
            'lights_and_sounds': ['sound', 'audio', 'lights', 'lighting', 'music', 'dj', 'speaker'],
            'photography': ['photo', 'photographer', 'pictures', 'camera'],
            'videography': ['video', 'videographer', 'film', 'recording'],
            'host': ['host', 'emcee', 'mc', 'master of ceremonies'],
            'styling': ['styling', 'decoration', 'flowers', 'floral', 'decor', 'theme'],
            'rental': ['rental', 'tables', 'chairs', 'tent', 'equipment', 'stage']
        }
        
        services_list: List[str] = []
        for service, keywords in service_keywords.items():
            if any(keyword in message_lower for keyword in keywords):
                services_list.append(service)
        data['services'] = services_list
        
        # Create a merged state to check what information we now have
        # This ensures we check against both existing state AND newly extracted data
        merged_state = {**conversation_state, **data}
        
        # Determine next stage based on what we have in the merged state
        if not merged_state.get('event_type'):
            return 'event_type', data
        elif not merged_state.get('guests'):
            return 'guest_count', data
        elif not merged_state.get('budget'):
            return 'budget', data
        elif not merged_state.get('date_mentioned'):
            return 'date', data
        elif not merged_state.get('services_needed') and not data.get('services') and 'all' not in message_lower:
            return 'services_needed', data
        else:
            # Mark services as confirmed if user mentioned them or said "all"
            if data.get('services') or 'all' in message_lower or 'everything' in message_lower:
                data['services_needed'] = True
            return 'recommendations', data
    
    def generate_question(self, stage: str, conversation_state: Dict[str, Any]) -> str:
        """Generate the next question based on the stage"""
        
        questions = {
            'greeting': "Hello! I'm your AI event planning assistant. I'll help you find the perfect venue and suppliers for your event. Let's start with the basics - what type of event are you planning? (e.g., wedding, corporate event, birthday party, concert)",
            
            'event_type': "Great! What type of event are you planning? For example:\n• Wedding\n• Corporate event/Conference\n• Birthday party\n• Concert or performance\n• Other celebration",
            
            'guest_count': f"Perfect! For your {conversation_state.get('event_type', 'event')}, how many guests are you expecting?",
            
            'budget': f"Excellent! For {conversation_state.get('guests', 'your')} guests, what's your total budget for the event? (This will help me recommend venues and services within your range)",
            
            'date': "When are you planning to hold this event? (You can provide a specific date or just the month/year)",
            
            'services_needed': "Now let's talk about services! Which of these would you like me to recommend?\n\n" + 
                             "📋 Available Services:\n" +
                             "1. 🍽️ Catering (food and beverages)\n" +
                             "2. 🎵 Lights and Sounds (audio-visual)\n" +
                             "3. 📸 Photography\n" +
                             "4. 🎥 Videography\n" +
                             "5. 🎤 Host/Emcee\n" +
                             "6. 💐 Styling and Flowers\n" +
                             "7. 🪑 Equipment Rental (tables, chairs, etc.)\n\n" +
                             "You can say 'all', mention specific ones, or say 'just the venue for now'"
        }
        
        return questions.get(stage, "How else can I help you with your event?")
    
    def get_venue_recommendations(self, conn: Any, requirements: Dict[str, Any]) -> List[Dict[str, Any]]:
        """Get venue recommendations based on requirements"""
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT 
                v.venue_id,
                v.venue_name,
                v.capacity,
                v.base_price,
                v.location,
                v.description,
                GROUP_CONCAT(va.amenity_name SEPARATOR ', ') as amenities
            FROM venues v
            LEFT JOIN venue_amenities va ON v.venue_id = va.venue_id
            WHERE v.availability_status = 'available'
            GROUP BY v.venue_id
        """
        
        cursor.execute(query)
        venues = cursor.fetchall()
        cursor.close()
        
        # Convert Decimal to float
        for venue in venues:
            if venue['base_price']:
                venue['base_price'] = float(venue['base_price'])
        
        # Train ML model if we have enough venues
        ml_predictions = {}
        if len(venues) >= 3:
            try:
                model, scaler = self.train_naive_bayes_model(venues, requirements)
                
                # Get ML predictions for all venues
                for venue in venues:
                    features = self.extract_venue_features(venue, requirements)
                    features_scaled = scaler.transform(features.reshape(1, -1))
                    
                    # Get probability of being a good match
                    probabilities = model.predict_proba(features_scaled)[0]
                    # Use probability of class 1 (good match)
                    ml_score = probabilities[1] if len(probabilities) > 1 else probabilities[0]
                    ml_predictions[venue['venue_id']] = ml_score * 100  # Scale to 0-100
            except Exception as e:
                # If ML fails, continue with rule-based scoring only
                print(f"ML training failed: {e}", file=sys.stderr)
        
        # Score venues using hybrid approach
        scored_venues: List[Dict[str, Any]] = []
        for venue in venues:
            # Get rule-based score
            rule_score = self.calculate_venue_score(venue, requirements)
            
            # Get ML score if available
            ml_score = ml_predictions.get(venue['venue_id'], rule_score)
            
            # Combine scores: 60% rule-based (interpretable), 40% ML (data-driven)
            final_score = (rule_score * 0.6) + (ml_score * 0.4)
            
            if final_score > 30:  # Minimum threshold
                scored_venues.append({
                    'id': venue['venue_id'],
                    'name': venue['venue_name'],
                    'capacity': venue['capacity'],
                    'price': venue['base_price'],
                    'location': venue['location'],
                    'description': venue['description'],
                    'amenities': venue['amenities'],
                    'score': round(final_score, 1),
                    'ml_confidence': round(ml_score, 1) if venue['venue_id'] in ml_predictions else None
                })
        
        # Sort by score
        scored_venues.sort(key=lambda x: x['score'], reverse=True)
        return scored_venues[:5]  # Top 5 for better selection
    
    def extract_venue_features(self, venue: Dict[str, Any], requirements: Dict[str, Any]) -> np.ndarray:
        """
        Extract numerical features for ML model
        Returns feature vector: [capacity_ratio, price_ratio, capacity_utilization, 
                                price_per_guest, budget_fit, amenity_count]
        """
        features = []
        
        # Feature 1: Capacity ratio (venue capacity / required guests)
        guests = requirements.get('guests', 50)
        capacity = venue['capacity']
        capacity_ratio = capacity / guests if guests > 0 else 1.0
        features.append(capacity_ratio)
        
        # Feature 2: Price ratio (venue price / total budget)
        budget = requirements.get('budget', 50000)
        price = venue['base_price'] or 10000
        price_ratio = price / budget if budget > 0 else 1.0
        features.append(price_ratio)
        
        # Feature 3: Capacity utilization (how well guest count fits)
        # 1.0 is perfect, decreases as mismatch increases
        if capacity >= guests:
            utilization = guests / capacity if capacity > 0 else 0.5
        else:
            utilization = capacity / guests if guests > 0 else 0.5
        features.append(utilization)
        
        # Feature 4: Price per guest
        price_per_guest = price / guests if guests > 0 else price / 50
        features.append(price_per_guest)
        
        # Feature 5: Budget fit score (normalized)
        # Target: venue should be 30-45% of total budget
        ideal_venue_price = budget * 0.375  # 37.5% midpoint
        budget_fit = 1.0 - abs(price - ideal_venue_price) / budget if budget > 0 else 0.5
        budget_fit = max(0, min(1, budget_fit))  # Clamp between 0 and 1
        features.append(budget_fit)
        
        # Feature 6: Amenity count (normalized)
        amenity_count = len(venue.get('amenities', '').split(',')) if venue.get('amenities') else 0
        amenity_score = min(amenity_count / 10.0, 1.0)  # Normalize to 0-1
        features.append(amenity_score)
        
        return np.array(features)
    
    def train_naive_bayes_model(self, venues: List[Dict[str, Any]], requirements: Dict[str, Any]) -> Tuple[GaussianNB, StandardScaler]:
        """
        Train a Naive Bayes classifier to predict venue suitability
        Uses synthetic labeling based on multiple criteria
        """
        if len(venues) == 0:
            # Return dummy model if no venues
            return GaussianNB(), StandardScaler()
        
        # Extract features for all venues
        X = np.array([self.extract_venue_features(v, requirements) for v in venues])
        
        # Generate labels based on multiple criteria (synthetic labeling)
        # This creates training data from the venues we have
        y = []
        for venue in venues:
            score = 0
            guests = requirements.get('guests', 50)
            budget = requirements.get('budget', 50000)
            
            # Criteria 1: Capacity match (good if 100%-150% of guest count)
            capacity_match = guests <= venue['capacity'] <= guests * 1.5
            if capacity_match:
                score += 40
            elif venue['capacity'] >= guests * 0.8:
                score += 25
            else:
                score += 10
            
            # Criteria 2: Price appropriateness (30-50% of budget)
            price = venue['base_price'] or 10000
            price_ok = 0.25 * budget <= price <= 0.50 * budget
            if price_ok:
                score += 35
            elif price <= 0.60 * budget:
                score += 20
            else:
                score += 5
            
            # Criteria 3: Amenities
            amenity_count = len(venue.get('amenities', '').split(',')) if venue.get('amenities') else 0
            if amenity_count >= 5:
                score += 15
            elif amenity_count >= 3:
                score += 10
            else:
                score += 5
            
            # Criteria 4: Value for money (price per guest per capacity)
            value_score = (venue['capacity'] / price) if price > 0 else 0
            if value_score > 0.02:  # Good value
                score += 10
            
            # Label: 1 = good match (score >= 70), 0 = poor match
            y.append(1 if score >= 70 else 0)
        
        y = np.array(y)
        
        # Ensure we have both classes
        if len(np.unique(y)) < 2:
            # If all venues are same class, artificially adjust one
            if len(y) > 1:
                y[np.argmax(X[:, 4])] = 1  # Best budget fit gets class 1
                y[np.argmin(X[:, 4])] = 0  # Worst budget fit gets class 0
        
        # Scale features
        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(X)
        
        # Train Naive Bayes
        model = GaussianNB()
        model.fit(X_scaled, y)
        
        return model, scaler
    
    def calculate_venue_score(self, venue: Dict[str, Any], requirements: Dict[str, Any]) -> float:
        """
        Calculate venue suitability score using enhanced multi-factor algorithm
        Provides better differentiation between similar venues
        """
        # Extract features
        guests = requirements.get('guests', 50)
        budget = requirements.get('budget', 50000)
        capacity = venue['capacity']
        price = venue['base_price'] or 10000
        
        # Component 1: Capacity match (0-25 points)
        # Use a scoring curve that rewards optimal fit
        capacity_ratio = capacity / guests if guests > 0 else 1.0
        
        if 1.0 <= capacity_ratio <= 1.2:  # Perfect: 100-120% of guests
            capacity_score = 25
        elif 1.2 < capacity_ratio <= 1.5:  # Good: 120-150%
            # Linear decay from 25 to 20
            capacity_score = 25 - ((capacity_ratio - 1.2) / 0.3) * 5
        elif 1.5 < capacity_ratio <= 2.0:  # Acceptable: 150-200%
            capacity_score = 20 - ((capacity_ratio - 1.5) / 0.5) * 8
        elif 0.85 <= capacity_ratio < 1.0:  # Slightly under
            capacity_score = 18
        elif 2.0 < capacity_ratio <= 3.0:  # Too large
            capacity_score = 12 - ((capacity_ratio - 2.0) / 1.0) * 7
        else:
            capacity_score = 5
        
        # Component 2: Budget optimization (0-30 points)
        # More granular budget scoring
        ideal_venue_budget = budget * 0.35  # 35% of total budget
        price_ratio = price / ideal_venue_budget if ideal_venue_budget > 0 else 10.0
        
        if 0.8 <= price_ratio <= 1.1:  # Within 80-110% of ideal
            budget_score = 30
        elif 0.6 <= price_ratio < 0.8:  # Better value (60-80%)
            budget_score = 28
        elif 1.1 < price_ratio <= 1.3:  # Slightly over ideal (110-130%)
            budget_score = 25 - ((price_ratio - 1.1) / 0.2) * 8
        elif 1.3 < price_ratio <= 1.6:  # Pushing budget (130-160%)
            budget_score = 17 - ((price_ratio - 1.3) / 0.3) * 10
        elif 0.4 <= price_ratio < 0.6:  # Much cheaper
            budget_score = 20
        elif price_ratio < 0.4:  # Too cheap (may be quality concerns)
            budget_score = 15
        else:  # Over budget
            budget_score = max(3, 7 - (price_ratio - 1.6) * 3)
        
        # Component 3: Value efficiency (0-20 points)
        # Price per capacity unit - rewards venues that offer better value
        price_per_capacity = price / capacity if capacity > 0 else float('inf')
        
        # Calculate optimal price per capacity for this budget/guest combo
        optimal_ppc = (budget * 0.35) / (guests * 1.2)  # Ideal venue at 120% capacity
        ppc_ratio = price_per_capacity / optimal_ppc if optimal_ppc > 0 else 10.0
        
        if 0.7 <= ppc_ratio <= 1.1:  # Great value
            value_score = 20
        elif 0.5 <= ppc_ratio < 0.7:  # Excellent bargain
            value_score = 18
        elif 1.1 < ppc_ratio <= 1.4:  # Decent value
            value_score = 15 - ((ppc_ratio - 1.1) / 0.3) * 7
        elif 1.4 < ppc_ratio <= 2.0:  # Poor value
            value_score = 8 - ((ppc_ratio - 1.4) / 0.6) * 5
        else:
            value_score = 3
        
        # Component 4: Amenities & features (0-15 points)
        amenity_list = venue.get('amenities', '').split(',') if venue.get('amenities') else []
        amenity_count = len([a for a in amenity_list if a.strip()])
        
        # Progressive scoring for amenities
        if amenity_count >= 6:
            amenity_score = 15
        elif amenity_count >= 4:
            amenity_score = 12
        elif amenity_count >= 2:
            amenity_score = 8
        elif amenity_count >= 1:
            amenity_score = 5
        else:
            amenity_score = 2
        
        # Component 5: Size appropriateness (0-10 points)
        # Bonus for venues that aren't too big or too small
        guests_per_sqft_score = 0
        if 1.0 <= capacity_ratio <= 1.4:
            guests_per_sqft_score = 10  # Perfect size
        elif 1.4 < capacity_ratio <= 2.0:
            guests_per_sqft_score = 7
        elif 0.9 <= capacity_ratio < 1.0:
            guests_per_sqft_score = 6
        else:
            guests_per_sqft_score = 3
        
        # Combine all components
        total_score = (capacity_score + budget_score + value_score + 
                      amenity_score + guests_per_sqft_score)
        
        # Normalize to 0-100 scale
        return min(100, max(0, total_score))
    
    def get_supplier_recommendations(self, conn: Any, requirements: Dict[str, Any], categories: List[str]) -> Dict[str, List[Dict[str, Any]]]:
        """Get supplier and service recommendations"""
        cursor = conn.cursor(dictionary=True)
        
        # Map user-friendly names to database categories
        category_map = {
            'catering': 'Catering',
            'lights_and_sounds': 'Lights and Sounds',
            'photography': 'Photography',
            'videography': 'Videography',
            'host': 'Host/Emcee',
            'styling': 'Styling and Flowers',
            'rental': 'Equipment Rental'
        }
        
        recommendations: Dict[str, List[Dict[str, Any]]] = {}
        
        for category_key in categories:
            db_category = category_map.get(category_key)
            if not db_category:
                continue
            
            query = """
                SELECT 
                    s.service_id,
                    s.service_name,
                    s.category,
                    s.description,
                    s.price,
                    sup.supplier_name,
                    sup.location,
                    sup.phone,
                    sup.email
                FROM services s
                JOIN suppliers sup ON s.supplier_id = sup.supplier_id
                WHERE s.category = %s AND sup.availability_status = 'available'
                ORDER BY s.price ASC
            """
            
            cursor.execute(query, (db_category,))
            services = cursor.fetchall()
            
            # Convert Decimal to float
            for service in services:
                if service['price']:
                    service['price'] = float(service['price'])
            
            if services:
                # Filter by budget if available
                if requirements.get('budget'):
                    filtered_services = self.filter_by_budget(services, requirements['budget'])
                    recommendations[db_category] = filtered_services[:2]  # Top 2 per category
                else:
                    recommendations[db_category] = services[:2]
        
        cursor.close()
        return recommendations
    
    def filter_by_budget(self, services: List[Dict[str, Any]], total_budget: float) -> List[Dict[str, Any]]:
        """Filter services by budget"""
        # Rough budget allocation
        allocations = {
            'Catering': 0.25,  # 25% of budget
            'Lights and Sounds': 0.15,
            'Photography': 0.12,
            'Videography': 0.12,
            'Host/Emcee': 0.08,
            'Styling and Flowers': 0.15,
            'Equipment Rental': 0.10
        }
        
        if not services:
            return []
        
        category = services[0]['category']
        budget_for_category = total_budget * allocations.get(category, 0.10)
        
        filtered: List[Dict[str, Any]] = []
        for service in services:
            if service['price'] <= budget_for_category * 1.3:  # Allow 30% flexibility
                filtered.append(service)
        
        return filtered if filtered else services  # Return all if none match
    
    def process_conversation(self, message: str, conversation_state: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        """Main conversation processing"""
        if conversation_state is None:
            conversation_state = {}
        
        # Determine current stage
        stage, extracted_data = self.determine_stage(conversation_state, message)
        
        # Update conversation state with extracted data
        if extracted_data:
            # Special handling for services - append instead of replace
            if 'services' in extracted_data and 'services' in conversation_state:
                existing_services = conversation_state.get('services', [])
                new_services = extracted_data.pop('services')
                extracted_data['services'] = list(set(existing_services + new_services))
            
            conversation_state.update(extracted_data)
        
        # Generate response
        if stage == 'recommendations':
            return self.generate_final_recommendations(conversation_state)
        else:
            # Ask next question
            question = self.generate_question(stage, conversation_state)
            
            # If we extracted data, acknowledge it
            acknowledgment = ""
            if 'event_type' in extracted_data:
                acknowledgment = f"Got it! A {extracted_data['event_type']} event. "
            if 'guests' in extracted_data:
                acknowledgment += f"For {extracted_data['guests']} guests. "
            if 'budget' in extracted_data:
                acknowledgment += f"With a budget of ₱{extracted_data['budget']:,}. "
            
            if acknowledgment:
                response = acknowledgment + "\n\n" + question
            else:
                response = question
            
            return {
                'success': True,
                'response': response,
                'stage': stage,
                'conversation_state': conversation_state,
                'needs_more_info': True
            }
    
    def generate_final_recommendations(self, conversation_state: Dict[str, Any]) -> Dict[str, Any]:
        """Generate comprehensive recommendations"""
        try:
            conn = mysql.connector.connect(**self.db_config)
            
            # Get venue recommendations
            venues = self.get_venue_recommendations(conn, conversation_state)
            
            # Get supplier recommendations
            services_needed = conversation_state.get('services', [])
            if not services_needed or 'all' in str(services_needed).lower():
                services_needed = ['catering', 'lights_and_sounds', 'photography', 
                                 'videography', 'host', 'styling', 'rental']
            
            suppliers = self.get_supplier_recommendations(conn, conversation_state, services_needed)
            
            conn.close()
            
            # Build response
            response = self.format_final_response(conversation_state, venues, suppliers)
            
            return {
                'success': True,
                'response': response,
                'venues': venues,
                'suppliers': suppliers,
                'conversation_state': conversation_state,
                'needs_more_info': False,
                'stage': 'complete'
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'conversation_state': conversation_state
            }
    
    def format_final_response(self, state: Dict[str, Any], venues: List[Dict[str, Any]], suppliers: Dict[str, List[Dict[str, Any]]]) -> str:
        """Format the final comprehensive recommendation"""
        event_type = state.get('event_type', 'event').title()
        guests = state.get('guests', 'N/A')
        budget = state.get('budget')
        
        response = f"🎉 Perfect! Here's your complete event plan for your {event_type}:\n\n"
        response += f"📊 Event Summary:\n"
        response += f"• Type: {event_type}\n"
        response += f"• Guests: {guests}\n"
        if budget:
            response += f"• Budget: ₱{budget:,}\n"
        
        # Venue count
        venue_count = len(venues) if venues else 0
        response += f"\n🏛️ Top Venue Recommendations ({venue_count} found):\n"
        if venue_count > 0:
            response += "I've found the best venues that match your requirements. Check them out below!\n"
        else:
            response += "Unfortunately, no venues match your exact criteria right now. Try adjusting your budget or guest count.\n"
        
        # Supplier count
        supplier_count = sum(len(services) for services in suppliers.values()) if suppliers else 0
        response += f"\n👥 Recommended Suppliers & Services ({supplier_count} found):\n"
        if supplier_count > 0:
            response += "I've curated the best suppliers for each service you need. Scroll down to see all options!\n"
        else:
            response += "I'm still working on finding the perfect suppliers for your event.\n"
        
        return response

def main() -> None:
    if len(sys.argv) < 2:
        print(json.dumps({
            'success': False,
            'error': 'No message provided'
        }))
        sys.exit(1)
    
    message: str = sys.argv[1]
    
    # Get conversation state if provided (for multi-turn conversations)
    conversation_state: Dict[str, Any] = {}
    if len(sys.argv) > 2:
        try:
            conversation_state = json.loads(sys.argv[2])
        except Exception:
            pass
    
    planner = ConversationalEventPlanner()
    result = planner.process_conversation(message, conversation_state)
    
    print(json.dumps(result))

if __name__ == '__main__':
    main()
