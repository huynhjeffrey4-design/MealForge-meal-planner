#!/bin/bash

# Check if gum is installed, install if not
check_install_gum() {
  if ! command -v gum &> /dev/null; then
    echo "Gum is required for this script. Installing gum..."
    
    if command -v brew &> /dev/null; then
      # macOS with Homebrew
      brew install gum
    elif command -v apt-get &> /dev/null; then
      # Debian/Ubuntu
      sudo mkdir -p /etc/apt/keyrings
      curl -fsSL https://repo.charm.sh/apt/gpg.key | sudo gpg --dearmor -o /etc/apt/keyrings/charm.gpg
      echo "deb [signed-by=/etc/apt/keyrings/charm.gpg] https://repo.charm.sh/apt/ * *" | sudo tee /etc/apt/sources.list.d/charm.list
      sudo apt update && sudo apt install gum
    elif command -v dnf &> /dev/null; then
      # Fedora/RHEL
      sudo dnf install -y gum
    elif command -v pacman &> /dev/null; then
      # Arch Linux
      sudo pacman -S gum
    else
      echo "Could not install gum automatically. Please install it manually from: https://github.com/charmbracelet/gum"
      exit 1
    fi
  fi
}

# Ensure gum is available
check_install_gum

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to install packages based on the OS
install_package() {
    local package_name="$1"
    
    if command_exists apt-get; then # Debian/Ubuntu
        sudo apt-get update
        sudo apt-get install -y "$package_name"
    elif command_exists dnf; then # Fedora
        sudo dnf install -y "$package_name"
    elif command_exists yum; then # CentOS/RHEL
        sudo yum install -y "$package_name"
    elif command_exists pacman; then # Arch Linux
        sudo pacman -S --noconfirm "$package_name"
    elif command_exists brew; then # macOS with Homebrew
        brew install "$package_name"
    else
        gum style --foreground 1 "Could not determine package manager. Please install $package_name manually."
        return 1
    fi
}

# Display fancy header
gum style \
  --border double \
  --border-foreground 212 \
  --foreground 212 \
  --align center \
  --width 50 \
  --margin "1" \
  --padding "1" \
  "PHP Application Setup Assistant"

# Check if Docker is installed
gum spin --spinner dot --title "Checking for Docker..." -- sleep 1

if command_exists docker; then
    gum style --foreground 10 "✓ Docker is already installed."
    docker --version | gum style --foreground 8
else
    gum style --foreground 3 "Docker not found. We'll need to install it."
    
    # Different installation methods based on OS
    if [ "$(uname)" == "Darwin" ]; then
        # macOS
        gum style --foreground 3 "Please install Docker Desktop for Mac from:"
        gum style --foreground 6 "https://www.docker.com/products/docker-desktop"
        gum input --prompt "Press Enter when Docker is installed to continue > " --value " "
    elif [ "$(uname)" == "Linux" ]; then
        # Linux
        gum style --foreground 3 "Installing Docker for Linux..."
        
        if command_exists apt-get; then
            # Debian/Ubuntu
            gum spin --spinner dot --title "Setting up Docker repositories..." -- bash -c "sudo apt-get update && sudo apt-get install -y apt-transport-https ca-certificates curl software-properties-common"
            gum spin --spinner dot --title "Adding Docker GPG key..." -- bash -c "curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -"
            gum spin --spinner dot --title "Adding Docker repository..." -- bash -c "sudo add-apt-repository \"deb [arch=amd64] https://download.docker.com/linux/ubuntu \$(lsb_release -cs) stable\""
            gum spin --spinner dot --title "Installing Docker..." -- bash -c "sudo apt-get update && sudo apt-get install -y docker-ce docker-ce-cli containerd.io"
            sudo usermod -aG docker "$USER"
        elif command_exists dnf; then
            # Fedora
            gum spin --spinner dot --title "Setting up Docker repositories..." -- bash -c "sudo dnf -y install dnf-plugins-core"
            gum spin --spinner dot --title "Adding Docker repository..." -- bash -c "sudo dnf config-manager --add-repo https://download.docker.com/linux/fedora/docker-ce.repo"
            gum spin --spinner dot --title "Installing Docker..." -- bash -c "sudo dnf install -y docker-ce docker-ce-cli containerd.io"
            sudo usermod -aG docker "$USER"
        else
            gum style --foreground 3 "Could not determine Linux distribution. Please follow Docker installation guide at:"
            gum style --foreground 6 "https://docs.docker.com/engine/install/"
            exit 1
        fi
        
        # Start Docker service
        gum spin --spinner dot --title "Starting Docker service..." -- bash -c "sudo systemctl enable docker && sudo systemctl start docker"
    else
        gum style --foreground 1 "Unsupported operating system. Please install Docker manually."
        exit 1
    fi
    
    # Verify Docker installation
    if command_exists docker; then
        gum style --foreground 10 "✓ Docker has been successfully installed."
        docker --version | gum style --foreground 8
    else
        gum style --foreground 1 "Docker installation failed. Please install Docker manually."
        exit 1
    fi
fi

# Check if Docker daemon is running
if ! docker info >/dev/null 2>&1; then
    gum style --foreground 1 "Docker daemon is not running. Starting Docker..."
    if [ "$(uname)" == "Linux" ]; then
        gum spin --spinner dot --title "Starting Docker daemon..." -- sudo systemctl start docker
    elif [ "$(uname)" == "Darwin" ]; then
        gum style --foreground 3 "Please start Docker Desktop manually."
        gum input --prompt "Press Enter when Docker is running to continue > " --value " "
    fi
fi

# Check if PHP is installed
gum spin --spinner dot --title "Checking for PHP..." -- sleep 1

if command_exists php; then
    gum style --foreground 10 "✓ PHP is already installed."
    php --version | head -n 1 | gum style --foreground 8
else
    gum style --foreground 3 "PHP not found. We'll need to install it."
    
    if [ "$(uname)" == "Darwin" ]; then
        # macOS
        if command_exists brew; then
            gum spin --spinner dot --title "Installing PHP..." -- brew install php@8.1
        else
            gum style --foreground 3 "Homebrew not found. Installing Homebrew first..."
            gum confirm "Install Homebrew?" && /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
            gum spin --spinner dot --title "Installing PHP..." -- brew install php@8.1
        fi
    elif [ "$(uname)" == "Linux" ]; then
        # Linux
        gum spin --spinner dot --title "Installing PHP..." -- install_package "php@8.1"
    fi
    
    # Verify PHP installation
    if command_exists php; then
        gum style --foreground 10 "✓ PHP has been successfully installed."
        php --version | head -n 1 | gum style --foreground 8
    else
        gum style --foreground 1 "PHP installation failed. Please install PHP manually."
        exit 1
    fi
fi

# Check if Composer is installed
gum spin --spinner dot --title "Checking for Composer..." -- sleep 1

if command_exists composer; then
    gum style --foreground 10 "✓ Composer is already installed."
    composer --version | gum style --foreground 8
else
    gum style --foreground 3 "Composer not found. We'll need to install it."
    
    # Install PHP extension dependencies for Linux
    if [ "$(uname)" == "Linux" ]; then
        gum spin --spinner dot --title "Installing PHP extensions..." -- bash -c "
            install_package \"php-cli\"
            install_package \"php-json\"
            install_package \"php-common\"
            install_package \"php-mbstring\"
            install_package \"unzip\"
        "
    fi
    
    # Download and install Composer
    gum spin --spinner dot --title "Downloading Composer..." -- bash -c "
        php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\"
        php composer-setup.php
        php -r \"unlink('composer-setup.php');\"
        sudo mv composer.phar /usr/local/bin/composer
    "
    
    # Verify Composer installation
    if command_exists composer; then
        gum style --foreground 10 "✓ Composer has been successfully installed."
        composer --version | gum style --foreground 8
    else
        gum style --foreground 1 "Composer installation failed. Please install Composer manually."
        exit 1
    fi
fi

# Check for a composer.json file in the current directory
gum spin --spinner dot --title "Checking for composer.json in the current directory..." -- sleep 1

if [ -f "composer.json" ]; then
    gum style --foreground 10 "✓ composer.json found."
    gum confirm "Run composer install now?" && composer install
else
    gum style --foreground 3 "No composer.json found in the current directory."
    if gum confirm "Initialize a new PHP project with composer init?"; then
        composer init
    fi
fi

# Display success message
gum style \
  --border double \
  --border-foreground 10 \
  --foreground 10 \
  --align center \
  --width 50 \
  --margin "1" \
  --padding "1" \
  "Setup Complete!"

# Show summary
gum style --foreground 6 "Your development environment now has:"
echo ""
echo "$(gum style --foreground 12 "Docker:") $(docker --version)"
echo "$(gum style --foreground 12 "PHP:") $(php --version | head -n 1)"
echo "$(gum style --foreground 12 "Composer:") $(composer --version)"
echo ""

gum style --foreground 3 "Note: If you installed Docker for the first time, you might need to log out and log back in for group changes to take effect."
gum style --foreground 10 "You're ready to start developing!"
