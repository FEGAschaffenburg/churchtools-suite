#!/bin/bash
# ChurchTools Suite - SSH Key Installation Script für Linux/Mac
# Hinweis: Dieses Script ist für Terminal/SSH auf dem FTP-Server gedacht

echo "╔════════════════════════════════════════════╗"
echo "║  SSH Key Installation for ChurchTools      ║"
echo "║  Server: ftp.feg.de                        ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Funktion: Schlüssel installieren
install_key() {
    local username=$1
    local pubkey=$2
    
    echo "Installing SSH key for user: $username"
    
    # Erstelle .ssh Verzeichnis
    mkdir -p ~/.ssh
    chmod 700 ~/.ssh
    
    # Füge Key zu authorized_keys hinzu (Duplikate vermeiden)
    if grep -q "$pubkey" ~/.ssh/authorized_keys 2>/dev/null; then
        echo "✓ Key bereits installiert für $username"
    else
        echo "$pubkey" >> ~/.ssh/authorized_keys
        chmod 600 ~/.ssh/authorized_keys
        echo "✓ Key installiert für $username"
    fi
}

# User 1: aschaffessh_plugin
# Führe dies aus als: aschaffessh_plugin
if [ "$USER" == "aschaffessh_plugin" ]; then
    install_key "aschaffessh_plugin" "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDJ2zUHh9KO/L8j0Jt5qKH6+gJy1+TaQIR2+Ml7oZdtODvbkx+qx3zVTPHmi7b4MElyxno7lfRXZjZi1pOALKFkddA4JsNAbNtFJICzwDHH0Zvvx9848BBqXM2elnJgIo/CnryzVINe4L6s1Vhikj9+G/Obo8d3efwRSxLGZRwNjg6WX2npYPMioOobndEwjCzFi19NXQEtT7rj7ndMLy5aVtBNaLa2GoU2LeUIGffeb2xNqgEqJokEbR2vL0inOeeuY0BJsD2TSQyiiEVNgjheMCJDcIBhDmtIjnNiwsKEABpbZp8VAkovf1/e2Cjl3KFHdjJG/bcYm4z/hSVHBrNbfbGfMJgcSsuVHPhU1scFg0LfVh/RW0syccFREJl2KetiIxw9NZCg3DiQt84w890tMW3edgVPDVdpEWgoJ3rgbpzUNQbsa8HrYiGQIE0bocYSiqXdgt9DELwn0RTRnbTl3/H/mRW30CBgKoW0PAboK1T2IebLbiIF51f5G25Yed77d4QybeXtxbgQpRceAOX94ItLGJhwAeaCSadFXkRrQhswErPpqezUdLROgIfrfuvG3qurCtjAUUh2MxIrG7Q9d9gqCcfdFbnhmX/1d3fpeIBh5i5id80wvtrrKnEyRXJbq6UQG7Lf0Y4iO9GmNxeDzB6WSUqvzo3OBGC1HTcQ/w== aschaffessh_plugin@ftp.feg.de"
    
# User 2: aschaffessh_test2
elif [ "$USER" == "aschaffessh_test2" ]; then
    install_key "aschaffessh_test2" "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDp0D8ISqWLNCS8IwxV6+jSZ8GUSymL63j3Qp+gXJOfdU1v/WkFEGYMAzAMi87c0MZxijdZGF3ckGAlQPA7bzwki/9Ej+VDPv63uENAGd8gj1vsrJ498WdXQf7GjyAtnl3mm6L3E+3+X4Sdxebmf1bgcFCT+gOO7cuqAduXj3pYJyY2e0z6aZ2aizsoL7Qt/oYivqw8deWYxnKk4ZGF4RJBKXRCob2NIYuHM9K4D+gn/9Cq35J1HhWiY6Nwl0G0QqRsDyH++1dBiT0mf1rMzPjTth9xZIlKOOkpiRpnAHRpJc7hyxBEXSDQJRU9lb8VQbldzMZL4a46bPLTyCFkhikUoz6Hh309R985gjgqOYYrAomzEIlmYk4ihnMBs9Nn0Q+Jrey4FAfeCoXFZ9Fv3XDRHKSQ5IqgLC2QyrfiffPydggM3nAfesb3Qj8yDmZ5b39M2nWzQKm0CZDoP/JqKwOQgWC6EW9hhkxhJH0KqxUMye89SeeTqvhU4NcuRwatAY9mIpeU3RIe/XE+FM3EW1aqWfelOmiGnA9UMoloR230+1RNmEFvivi8MoXdHbfSF5IK8FJ2ZMUrUJMKLG7jcCmKhfiVGXD0jD0MReYhm718i4/c81ANny90LPU+Nb+F9uXviWXk7UVCPgx7LnYVbll1RMGs4MhEsaOU1qCoVSbm5Q== aschaffessh_test2@ftp.feg.de"

else
    echo "✗ Error: Please run this script as aschaffessh_plugin or aschaffessh_test2"
    echo ""
    echo "Usage:"
    echo "  su - aschaffessh_plugin"
    echo "  bash ~/install-ssh-keys.sh"
    echo ""
    echo "Or:"
    echo "  su - aschaffessh_test2"
    echo "  bash ~/install-ssh-keys.sh"
    exit 1
fi

echo ""
echo "Installation complete!"
echo "✓ Test connection: ssh plugin-test 'whoami'"
