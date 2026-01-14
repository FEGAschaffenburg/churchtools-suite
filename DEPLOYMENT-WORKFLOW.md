# ChurchTools Suite - Deployment Workflow

## 🚀 Vor dem produktiven Einsatz: Testing auf 2 Test-Servern

Dieses Dokument beschreibt den optimalen Deployment-Workflow zur Fehler-Vermeidung.

---

## 📋 SSH-Test-Server Konfiguration

### Verfügbare Test-Server

| Alias | Host | User | Port | Zweck |
|-------|------|------|------|-------|
| `plugin-test` | ftp.feg.de | aschaffessh_plugin | 22 | Plugin-Test |
| `test2-test` | ftp.feg.de | aschaffessh_test2 | 22 | Alternative Test |

**Konfiguration:** `~/.ssh/config` (bereits eingerichtet)

---

## 🔄 Deployment-Workflow

### Phase 1: Lokale Entwicklung & Testing

```bash
# 1. Änderungen durchführen
cd c:\Users\nauma\OneDrive\Plugin_neu\churchtools-suite

# 2. Lokal testen
php -l churchtools-suite.php                    # Syntax-Check
php -l includes/class-churchtools-suite.php     # Syntax-Check
php -l includes/elementor/*.php                 # Widget-Check

# 3. Code reviewen
git diff                                        # Änderungen prüfen
git status                                      # Status prüfen

# 4. Commiten
git add -A
git commit -m "feat: Description"
```

### Phase 2: Deploy auf Test-Server 1

```bash
# 1. ZIP erstellen
cd scripts
.\create-wp-zip.ps1 -Version "1.0.3.X"  # Version nach Bedarf

# 2. Auf plugin-test deployen
cd c:\privat
scp churchtools-suite-1.0.3.X.zip plugin-test:~/churchtools-suite.zip

# 3. Entpacken und testen
ssh plugin-test << 'EOF'
    cd ~
    rm -rf churchtools-suite
    unzip -q churchtools-suite.zip
    rm churchtools-suite.zip
    
    # Syntax-Checks
    php -l churchtools-suite/churchtools-suite.php
    php -l churchtools-suite/includes/class-churchtools-suite.php
    
    # Version überprüfen
    grep "Version:" churchtools-suite/churchtools-suite.php
    
    echo "✓ plugin-test Deployment erfolgreich"
EOF
```

### Phase 3: Deploy auf Test-Server 2 (Alternative)

```bash
# Gleicher Prozess auf test2-test
scp churchtools-suite-1.0.3.X.zip test2-test:~/churchtools-suite.zip

ssh test2-test << 'EOF'
    cd ~
    rm -rf churchtools-suite
    unzip -q churchtools-suite.zip
    rm churchtools-suite.zip
    
    php -l churchtools-suite/churchtools-suite.php
    grep "Version:" churchtools-suite/churchtools-suite.php
    
    echo "✓ test2-test Deployment erfolgreich"
EOF
```

### Phase 4: Push zu GitHub & Production

```bash
# 1. Nach erfolgreichem Test: Push
cd c:\Users\nauma\OneDrive\Plugin_neu\churchtools-suite
git push
git tag v1.0.3.X
git push --tags

# 2. GitHub Release erstellen
gh release create v1.0.3.X c:\privat\churchtools-suite-1.0.3.X.zip \
    --title "Release Title" \
    --notes "Beschreibung"

# 3. Auf Production deployen (Port 22073)
scp -P 22073 churchtools-suite-1.0.3.X.zip feg-plugin:~/churchtools-suite.zip

ssh -p 22073 feg-plugin << 'EOF'
    cd ~
    rm -rf churchtools-suite
    unzip -q churchtools-suite.zip
    rm churchtools-suite.zip
    
    php -l churchtools-suite/churchtools-suite.php
    echo "✓ Production Deployment erfolgreich"
EOF
```

---

## 🧪 Testing auf Test-Servern

### Schnelle Verbindung

```bash
# Verbinden
ssh plugin-test    # oder: ssh test2-test

# Schnelle Checks
php -l churchtools-suite/churchtools-suite.php
php -l churchtools-suite/includes/class-churchtools-suite.php
grep "Version:" churchtools-suite/churchtools-suite.php

# Oder mit Direct SSH-Command
ssh plugin-test "php -l churchtools-suite/churchtools-suite.php && grep 'Version:' churchtools-suite/churchtools-suite.php"
```

### Plugin-Funktionalität testen

```bash
ssh plugin-test << 'EOF'
    # Mit WordPress prüfen (wenn WP installiert)
    wp plugin list                          # Plugins anzeigen
    wp churchtools-suite:test-connection    # Plugin-Funktionalität testen
    
    # Oder: Logs prüfen
    tail -20 log/error.log
EOF
```

---

## 📊 Checkliste vor Production-Deployment

- [ ] Änderungen lokal durchgeführt & committed
- [ ] ZIP erstellt (0.34 MB Standard-Größe)
- [ ] Syntax-Check erfolgreich (alle .php Dateien)
- [ ] Auf `plugin-test` deployed & getestet
- [ ] Optional: Auf `test2-test` gegengetestet
- [ ] GitHub Push & Release erstellt
- [ ] Production Deploy durchgeführt
- [ ] Production Tests bestanden

---

## 🆘 Notfall-Rollback

Falls auf Production etwas schiefgeht:

```bash
# 1. Letzte stabile Version deployen
cd c:\privat
scp -P 22073 churchtools-suite-1.0.3.STABLE.zip feg-plugin:~/churchtools-suite.zip

# 2. Zurück updaten
ssh -p 22073 feg-plugin << 'EOF'
    cd ~
    rm -rf churchtools-suite
    unzip -q churchtools-suite.zip
    rm churchtools-suite.zip
    echo "✓ Rollback erfolgreich"
EOF

# 3. Issue debuggen auf Test-Server
ssh plugin-test  # Problem lokal reproduzieren & fixen
```

---

## 📝 Beispiel: Vollständiger Deployment-Workflow

```bash
# 1. Änderung & Test lokal
git add -A && git commit -m "fix: Elementor widget error handling"

# 2. ZIP erstellen
cd scripts && .\create-wp-zip.ps1 -Version "1.0.3.20"

# 3. Test-Deploy
cd c:\privat && scp churchtools-suite-1.0.3.20.zip plugin-test:~/ && \
ssh plugin-test "cd ~ && rm -rf churchtools-suite && unzip -q churchtools-suite-1.0.3.20.zip && \
php -l churchtools-suite/churchtools-suite.php && echo '✓ Test OK'"

# 4. Production-Deploy (bei erfolgreicher Test)
git push && git tag v1.0.3.20 && git push --tags && \
scp -P 22073 churchtools-suite-1.0.3.20.zip feg-plugin:~/ && \
ssh -p 22073 feg-plugin "cd ~ && rm -rf churchtools-suite && unzip -q churchtools-suite-1.0.3.20.zip && \
php -l churchtools-suite/churchtools-suite.php && echo '✓ Production OK'"
```

---

## 🔐 SSH-Keys Verwaltung

Die RSA-Keys sind in `~/.ssh/` gespeichert:
- `id_rsa_plugin_test` / `id_rsa_plugin_test.pub` (plugin-test)
- `id_rsa_test2_test` / `id_rsa_test2_test.pub` (test2-test)

Für neue Systeme: Keys kopieren und SSH-Config sync halten.

---

**Letztes Update:** 14. Januar 2026  
**Workflow-Version:** 1.0
