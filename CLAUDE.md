# Envago - Product Context for Claude Code

## What is Envago
Envago is a tour marketplace website for Brazil, like OLX or Booking.com but focused on tours/activities. 
Website: envago.live

**Scope**: National Brazil. Jeri is our launch city for Aug 2026, not the entire product.

## Business Model
1. **For tourist**: Searches "buggy Jeri" on Google → lands on operator page at envago.live → books and pays online
2. **For operator**: Free page setup. 10% commission only on bookings from Envago ads. Direct sales = 0% commission
3. **Flow**: Online booking → operator gets notification → confirms in dashboard → PIX payout D+1 after tour

## Target Customer
Tour operators in Brazil: buggies, boats, dune tours, kite lessons, etc.
Main pain: Vehicles with 4+ seats running empty in low season.

## Key Differentiators
1. Free operator page created from Excel spreadsheet upload
2. Envago pays for Google Ads to drive traffic 
3. Live seat counter: shows "X seats left today" to create urgency
4. No monthly fee, no setup cost. Pay only if you sell

## Technical Notes
1. **Input**: Excel/spreadsheet parser for tours list, price, duration, capacity
2. **Output**: Strict JSON schema for envago.live API
3. **Notifications**: New booking alerts for operator confirmation
4. **Payment**: PIX D+1 after operator confirms tour happened

## Important Constraints
1. Never promise "WhatsApp booking". Always "online booking on envago.live"
2. Operator must confirm booking in dashboard before payout
3. Product is national. Use Jeri as example, but don't limit messaging to Jeri only
4. We already operate in São Miguel do Gostoso = social proof

## Language Guidelines
**Use with operators:**
- "Tour website" instead of "marketplace" for non-technical audience
- "Like OLX" as marketplace analogy 
- "Empty seats" instead of "low occupancy"
- "Online booking" instead of "booking"

**Avoid:**
- "OTA", "SaaS", "marketplace" without analogy
- "WhatsApp booking"
- Technical jargon

## Launch Context
Starting with Jericoacoara Aug 2026 because high demand + clear pain point. Expansion to other Brazilian beach destinations follows.
