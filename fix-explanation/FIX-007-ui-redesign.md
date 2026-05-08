# FIX-007: Full UI Redesign — Modern Dark Navy Theme

## What Changed
All 3 screens redesigned from scratch. No logic was changed — only styles and layout.

### Design System
- **Background**: `#0F172A` (dark navy) for headers
- **Surface**: `#F1F5F9` (light gray-blue) for screen backgrounds
- **Cards**: `#FFFFFF` white with `#F1F5F9` backgrounds
- **Primary**: `#1D4ED8` / `#3B82F6` blue
- **Success/Live**: `#22C55E` green
- **Danger/Stop**: `#DC2626` red
- **Text**: `#0F172A` primary, `#64748B` secondary, `#94A3B8` muted

### LoginScreen
- Dark hero section (top half) with 🚐 icon, app name, tagline
- White bottom sheet (bottom half) sliding up with form
- Labeled input fields with focus border highlight
- Rounded blue Sign In button

### DriverScreen
- Dark navy header showing role ("DRIVER") + user name + sign out button
- Jeep list as tappable cards with selection state (blue border + badge)
- Live stats card (dark background) showing lat/lng/speed/heading + update counter
- Green "Start Tracking" / Red "Stop Tracking" button at the bottom

### PassengerScreen (pre-map)
- Matching dark header
- Horizontal scrollable jeep chips
- Dark vehicle card with green "Live" badge when tracking
- 2x2 coordinate grid cards

## How to Test
1. Open app → verify Login screen looks modern
2. Log in as driver → verify header shows name, jeep cards are styled
3. Start tracking → verify dark stats card appears with live coordinates
4. Log in as passenger → verify map + info bar renders correctly
