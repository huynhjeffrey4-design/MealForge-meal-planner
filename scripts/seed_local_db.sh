#!/bin/bash

# Check if gum is installed
if ! command -v gum &> /dev/null; then
    echo "This script requires 'gum' to be installed."
    echo "Install it with 'brew install gum' (macOS) or follow instructions at https://github.com/charmbracelet/gum"
    exit 1
fi

# Title and intro
gum style --border normal --margin "1" --padding "1 2" --border-foreground 212 "Local Database Seeder"

# Show a spinner with information message
gum spin --spinner dot --title "Checking prerequisites..." -- sleep 1.5

# Check if mysql is available
if ! command -v mysql &> /dev/null; then
    gum style --foreground 9 "❌ MySQL CLI not found. Please install it first."
    exit 1
else
    gum style --foreground 10 "✓ MySQL CLI found"
fi

# Confirm database seeding
if gum confirm "Seed the local database with test data?" --default=false; then
    echo ""
    gum style --foreground 14 "📝 Database seeding in progress..."
    
    # Show a spinner while the database is being seeded
    gum spin --spinner line --title "Running MySQL import..." -- \
        mysql -h 127.0.0.1 -u root -ppassword cse442_2025_spring_team_v_db < tests/Support/Data/dump.sql
    
    # Check if the command succeeded
    if [ $? -eq 0 ]; then
        echo ""
        gum style --border rounded --margin "1" --padding "0 2" --border-foreground 10 "✅ Database seeded successfully!"
    else
        echo ""
        gum style --border rounded --margin "1" --padding "0 2" --border-foreground 9 "❌ Database seeding failed"
    fi
else
    gum style --foreground 11 "Database seeding cancelled"
fi
