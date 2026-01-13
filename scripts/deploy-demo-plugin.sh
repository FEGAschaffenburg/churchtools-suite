#!/bin/bash
# Demo Plugin SSH Deployment v1.0.5.19
# Deployment für churchtools-suite-demo auf Production

set -e

echo "=========================================="
echo "ChurchTools Suite Demo - SSH Deployment"
echo "=========================================="
echo ""

# Konfiguration
PLUGIN_NAME="churchtools-suite-demo"
VERSION="1.0.5.19"
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"
ZIP_PATH="/tmp/${ZIP_NAME}"
REMOTE_USER="$1"
REMOTE_HOST="$2"
REMOTE_PATH="/var/www/html"
WP_PLUGINS_PATH="${REMOTE_PATH}/wp-content/plugins"

# Validierung
if [ -z "$REMOTE_USER" ] || [ -z "$REMOTE_HOST" ]; then
    echo "❌ Fehler: Remote-Zugang erforderlich"
    echo ""
    echo "Verwendung:"
    echo "  bash deploy-demo-plugin.sh USERNAME HOSTNAME"
    echo ""
    echo "Beispiel:"
    echo "  bash deploy-demo-plugin.sh user domain.de"
    echo ""
    exit 1
fi

# ZIP überprüfen
if [ ! -f "C:\\privat\\${ZIP_NAME}" ]; then
    echo "❌ Fehler: ZIP nicht gefunden: C:\\privat\\${ZIP_NAME}"
    echo ""
    echo "Bitte zunächst das ZIP erstellen:"
    echo "  powershell -File create-demo-zip.ps1"
    exit 1
fi

echo "📦 Plugin: $PLUGIN_NAME"
echo "📌 Version: $VERSION"
echo "🌐 Remote: $REMOTE_USER@$REMOTE_HOST"
echo "📂 Pfad: $WP_PLUGINS_PATH"
echo ""

# Bestätigung
read -p "Möchtest du fortfahren? (j/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Jj]$ ]]; then
    echo "Deployment abgebrochen."
    exit 1
fi

echo ""
echo "=== SCHRITT 1: Verbindung testen ==="
if ssh -o ConnectTimeout=5 "$REMOTE_USER@$REMOTE_HOST" "echo '✅ SSH OK'" 2>&1; then
    echo "✅ SSH-Verbindung OK"
else
    echo "❌ SSH-Verbindung fehlgeschlagen"
    exit 1
fi

echo ""
echo "=== SCHRITT 2: ZIP hochladen ==="
echo "Kopiere $ZIP_NAME zu $REMOTE_HOST..."
scp -r "C:\\privat\\${ZIP_NAME}" "$REMOTE_USER@$REMOTE_HOST:/tmp/"
echo "✅ ZIP hochgeladen"

echo ""
echo "=== SCHRITT 3: Plugin deaktivieren ==="
ssh "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_PATH && wp plugin deactivate $PLUGIN_NAME 2>/dev/null || true"
echo "✅ Plugin deaktiviert (oder war nicht aktiv)"

echo ""
echo "=== SCHRITT 4: Alte Version löschen ==="
ssh "$REMOTE_USER@$REMOTE_HOST" "rm -rf $WP_PLUGINS_PATH/$PLUGIN_NAME"
echo "✅ Alte Version gelöscht"

echo ""
echo "=== SCHRITT 5: Neue Version installieren ==="
ssh "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_PATH && wp plugin install /tmp/$ZIP_NAME --activate"
echo "✅ Neue Version installiert und aktiviert"

echo ""
echo "=== SCHRITT 6: Version überprüfen ==="
NEW_VERSION=$(ssh "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_PATH && wp plugin get $PLUGIN_NAME --field=version")
echo "Installierte Version: $NEW_VERSION"

if [ "$NEW_VERSION" = "$VERSION" ]; then
    echo "✅ Version OK: $NEW_VERSION"
else
    echo "⚠️  Warnung: Erwartete Version $VERSION, aber $NEW_VERSION gefunden"
fi

echo ""
echo "=== SCHRITT 7: Cleanup ==="
ssh "$REMOTE_USER@$REMOTE_HOST" "rm /tmp/$ZIP_NAME"
echo "✅ Temp-Dateien gelöscht"

echo ""
echo "=========================================="
echo "✅ Deployment erfolgreich!"
echo "=========================================="
echo ""
echo "Plugin: $PLUGIN_NAME"
echo "Version: $NEW_VERSION"
echo "Status: Aktiv"
echo ""
echo "Nächste Schritte:"
echo "1. Admin-Panel überprüfen: https://domain.de/wp-admin/"
echo "2. Plugin-Einstellungen testen"
echo "3. Keine JavaScript-Fehler in Console?"
echo "4. Funktionalität testen"
echo ""
