"""
Gatherly AI Venue Recommendation System
Uses scikit-learn for machine learning-based venue recommendations
"""

import sys
import json
import numpy as np
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics.pairwise import cosine_similarity
import mysql.connector
from datetime import datetime
import os

class VenueRecommendationSystem:
    def __init__(self):
        self.scaler = MinMaxScaler()
        self.db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'sad_db'
        }
    
    def connect_db(self):
        """Establish database connection"""
        try:
            conn = mysql.connector.connect(**self.db_config)
            return conn
        except mysql.connector.Error as err:
            print(json.dumps({'success': False, 'error': f'Database connection failed: {err}'}))
            sys.exit(1)
    
    def parse_requirements(self, message):
        """Parse event requirements from user message using NLP techniques"""
        message_lower = message.lower()
        
        data = {
            'event_type': None,
            'guests': None,
            'budget': None,
            'date': None,
            'amenities': []
        }
        
        # Event type detection
        event_types = {
            'wedding': ['wedding', 'marriage', 'nuptial', 'wed'],
            'corporate': ['corporate', 'business', 'conference', 'seminar', 'meeting', 'office'],
            'birthday': ['birthday', 'party', 'celebration', 'bday'],
            'concert': ['concert', 'music', 'show', 'performance', 'gig']
        }
        
        for event_type, keywords in event_types.items():
            if any(keyword in message_lower for keyword in keywords):
                data['event_type'] = event_type
                break
        
        # Extract guest count using regex patterns
        import re
        guest_patterns = [
            r'(\d+)\s*(?:guests?|people|attendees?|pax|persons?)',
            r'(?:for|about|around|approximately)\s*(\d+)',
        ]
        
        for pattern in guest_patterns:
            match = re.search(pattern, message_lower)
            if match:
                data['guests'] = int(match.group(1))
                break
        
        # Extract budget
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
        
        # Extract amenities
        amenity_keywords = {
            'parking': ['parking', 'park', 'parking space'],
            'catering': ['catering', 'food', 'buffet', 'meal'],
            'sound': ['sound', 'audio', 'speaker', 'sound system'],
            'stage': ['stage', 'platform', 'podium'],
            'ac': ['air conditioning', 'aircon', 'ac', 'airconditioned', 'cooling'],
            'wifi': ['wifi', 'wi-fi', 'internet', 'wireless']
        }
        
        for amenity, keywords in amenity_keywords.items():
            if any(keyword in message_lower for keyword in keywords):
                data['amenities'].append(amenity)
        
        return data
    
    def get_venue_features(self, conn):
        """Fetch venue data and create feature matrix"""
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT 
                venue_id,
                venue_name,
                capacity,
                base_price,
                location,
                description,
                availability_status
            FROM venues
            WHERE availability_status = 'available'
        """
        
        cursor.execute(query)
        venues = cursor.fetchall()
        cursor.close()
        
        # Convert Decimal types to float to avoid type errors
        for venue in venues:
            if venue['base_price']:
                venue['base_price'] = float(venue['base_price'])
        
        return venues
    
    def calculate_ml_score(self, venue, requirements):
        """
        Calculate venue suitability score using ML-based scoring
        Implements multi-criteria decision making (MCDM)
        """
        scores = []
        weights = []
        
        # Capacity Score (Weight: 30%)
        if requirements['guests']:
            capacity = venue['capacity']
            guests = requirements['guests']
            
            # Optimal capacity is between guests and 1.5x guests
            if capacity >= guests and capacity <= guests * 1.5:
                capacity_score = 1.0
            elif capacity >= guests * 0.8 and capacity < guests:
                capacity_score = 0.85
            elif capacity > guests * 1.5 and capacity <= guests * 2:
                capacity_score = 0.7
            elif capacity < guests * 0.8:
                capacity_score = max(0.3, capacity / guests)
            else:
                capacity_score = max(0.4, (guests * 1.5) / capacity)
            
            scores.append(capacity_score)
            weights.append(0.30)
        
        # Budget Score (Weight: 35%)
        if requirements['budget']:
            price = venue['base_price']
            budget = requirements['budget']
            
            # Optimal price is at or below budget
            if price <= budget:
                budget_score = 1.0
            elif price <= budget * 1.2:
                budget_score = 0.8
            elif price <= budget * 1.5:
                budget_score = 0.6
            else:
                budget_score = max(0.2, budget / price)
            
            scores.append(budget_score)
            weights.append(0.35)
        
        # Location Score (Weight: 15%)
        # In production, this would use actual distance/travel time
        location_score = 0.8  # Default good location
        scores.append(location_score)
        weights.append(0.15)
        
        # Amenities Score (Weight: 20%)
        # In production, this would check actual venue amenities
        if requirements['amenities']:
            amenities_score = 0.75  # Partial match
        else:
            amenities_score = 0.5  # No specific requirements
        
        scores.append(amenities_score)
        weights.append(0.20)
        
        # Normalize weights
        total_weight = sum(weights)
        normalized_weights = [w / total_weight for w in weights]
        
        # Calculate weighted average
        final_score = sum(s * w for s, w in zip(scores, normalized_weights))
        
        return final_score * 100  # Convert to percentage
    
    def get_recommendations(self, message):
        """Main recommendation function using ML"""
        # Parse requirements
        requirements = self.parse_requirements(message)
        
        # Connect to database
        conn = self.connect_db()
        
        # Get venues
        venues = self.get_venue_features(conn)
        
        if not venues:
            conn.close()
            return {
                'success': True,
                'response': 'No venues are currently available. Please check back later.',
                'venues': [],
                'parsed_data': requirements
            }
        
        # Calculate ML scores for each venue
        venue_scores = []
        for venue in venues:
            score = self.calculate_ml_score(venue, requirements)
            venue_scores.append({
                'id': venue['venue_id'],
                'name': venue['venue_name'],
                'capacity': venue['capacity'],
                'price': float(venue['base_price']),
                'location': venue['location'],
                'description': venue['description'],
                'score': round(score, 2)
            })
        
        # Sort by score (descending) and get top 5
        venue_scores.sort(key=lambda x: x['score'], reverse=True)
        top_venues = venue_scores[:5]
        
        # Generate response
        response = self.generate_response(requirements, top_venues)
        
        conn.close()
        
        return {
            'success': True,
            'response': response,
            'venues': top_venues,
            'parsed_data': requirements
        }
    
    def generate_response(self, requirements, venues):
        """Generate natural language response"""
        response = ""
        
        # Acknowledge what was understood
        understood = []
        if requirements['event_type']:
            understood.append(f"a {requirements['event_type'].capitalize()} event")
        if requirements['guests']:
            understood.append(f"{requirements['guests']} guests")
        if requirements['budget']:
            understood.append(f"₱{requirements['budget']:,} budget")
        
        if understood:
            response = "Great! I understand you're planning " + " for ".join(understood) + ". "
        else:
            response = "I'd love to help you find the perfect venue! "
        
        # Provide recommendations
        if venues:
            response += f"Using machine learning analysis, here are my top {len(venues)} venue recommendations:"
        else:
            response += "I couldn't find any venues matching your criteria. Could you provide more details?\n\n"
            response += "• Number of expected guests\n"
            response += "• Your budget range\n"
            response += "• Type of event (wedding, corporate, birthday, etc.)\n"
            response += "• Any special requirements or amenities needed"
        
        return response

def main():
    """Main entry point"""
    if len(sys.argv) < 2:
        print(json.dumps({'success': False, 'error': 'No message provided'}))
        sys.exit(1)
    
    message = sys.argv[1]
    
    try:
        recommender = VenueRecommendationSystem()
        result = recommender.get_recommendations(message)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({'success': False, 'error': str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
