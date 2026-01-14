# SSH Public Keys für ftp.feg.de

## Installation

Die Public Keys müssen auf ftp.feg.de in den `authorized_keys` Dateien der jeweiligen Benutzer installiert werden.

---

## 🔑 Key 1: aschaffessh_plugin

**Installation auf dem Server:**

```bash
ssh aschaffessh_plugin@ftp.feg.de

mkdir -p ~/.ssh
chmod 700 ~/.ssh

cat >> ~/.ssh/authorized_keys << 'EOF'
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDJ2zUHh9KO/L8j0Jt5qKH6+gJy1+TaQIR2+Ml7oZdtODvbkx+qx3zVTPHmi7b4MElyxno7lfRXZjZi1pOALKFkddA4JsNAbNtFJICzwDHH0Zvvx9848BBqXM2elnJgIo/CnryzVINe4L6s1Vhikj9+G/Obo8d3efwRSxLGZRwNjg6WX2npYPMioOobndEwjCzFi19NXQEtT7rj7ndMLy5aVtBNaLa2GoU2LeUIGffeb2xNqgEqJokEbR2vL0inOeeuY0BJsD2TSQyiiEVNgjheMCJDcIBhDmtIjnNiwsKEABpbZp8VAkovf1/e2Cjl3KFHdjJG/bcYm4z/hSVHBrNbfbGfMJgcSsuVHPhU1scFg0LfVh/RW0syccFREJl2KetiIxw9NZCg3DiQt84w890tMW3edgVPDVdpEWgoJ3rgbpzUNQbsa8HrYiGQIE0bocYSiqXdgt9DELwn0RTRnbTl3/H/mRW30CBgKoW0PAboK1T2IebLbiIF51f5G25Yed77d4QybeXtxbgQpRceAOX94ItLGJhwAeaCSadFXkRrQhswErPpqezUdLROgIfrfuvG3qurCtjAUUh2MxIrG7Q9d9gqCcfdFbnhmX/1d3fpeIBh5i5id80wvtrrKnEyRXJbq6UQG7Lf0Y4iO9GmNxeDzB6WSUqvzo3OBGC1HTcQ/w== aschaffessh_plugin@ftp.feg.de
EOF

chmod 600 ~/.ssh/authorized_keys

# Test: Sollte sich ohne Passwort-Prompt verbinden lassen
exit
```

**Verbindung von lokal testen:**
```bash
ssh plugin-test "whoami"
```

---

## 🔑 Key 2: aschaffessh_test2

**Installation auf dem Server:**

```bash
ssh aschaffessh_test2@ftp.feg.de

mkdir -p ~/.ssh
chmod 700 ~/.ssh

cat >> ~/.ssh/authorized_keys << 'EOF'
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDp0D8ISqWLNCS8IwxV6+jSZ8GUSymL63j3Qp+gXJOfdU1v/WkFEGYMAzAMi87c0MZxijdZGF3ckGAlQPA7bzwki/9Ej+VDPv63uENAGd8gj1vsrJ498WdXQf7GjyAtnl3mm6L3E+3+X4Sdxebmf1bgcFCT+gOO7cuqAduXj3pYJyY2e0z6aZ2aizsoL7Qt/oYivqw8deWYxnKk4ZGF4RJBKXRCob2NIYuHM9K4D+gn/9Cq35J1HhWiY6Nwl0G0QqRsDyH++1dBiT0mf1rMzPjTth9xZIlKOOkpiRpnAHRpJc7hyxBEXSDQJRU9lb8VQbldzMZL4a46bPLTyCFkhikUoz6Hh309R985gjgqOYYrAomzEIlmYk4ihnMBs9Nn0Q+Jrey4FAfeCoXFZ9Fv3XDRHKSQ5IqgLC2QyrfiffPydggM3nAfesb3Qj8yDmZ5b39M2nWzQKm0CZDoP/JqKwOQgWC6EW9hhkxhJH0KqxUMye89SeeTqvhU4NcuRwatAY9mIpeU3RIe/XE+FM3EW1aqWfelOmiGnA9UMoloR230+1RNmEFvivi8MoXdHbfSF5IK8FJ2ZMUrUJMKLG7jcCmKhfiVGXD0jD0MReYhm718i4/c81ANny90LPU+Nb+F9uXviWXk7UVCPgx7LnYVbll1RMGs4MhEsaOU1qCoVSbm5Q== aschaffessh_test2@ftp.feg.de
EOF

chmod 600 ~/.ssh/authorized_keys

# Test: Sollte sich ohne Passwort-Prompt verbinden lassen
exit
```

**Verbindung von lokal testen:**
```bash
ssh test2-test "whoami"
```

---

## 🔒 Key-Informationen

| Property | Value |
|----------|-------|
| Typ | RSA |
| Größe | 4096 bits |
| Format | OpenSSH |
| Generiert | 2026-01-14 |
| Fingerprint plugin-test | `SHA256:MnDQ2NJDXftqgQNXZt66oFDOK1M9SK7AGPtGtzvtZQA` |
| Fingerprint test2-test | `SHA256:7TaGr6YiiQH150rFqip+vFToMWlSoMEBmHaHfutkR94` |

---

## 🚀 Nach Installation

Sobald die Keys auf dem Server installiert sind, können Sie den schnellen Deploy-Workflow nutzen:

```bash
# Test-Server Deploy
pwsh scripts/deploy-test-servers.ps1 -Version "1.0.3.20"

# Production Deploy (nach erfolgreichem Test)
pwsh scripts/deploy-production.ps1 -Version "1.0.3.20"
```

---

## ⚠️ Sicherheitsnotes

- **Private Keys:** NIEMALS teilen oder in Git committen
- **Speicherort:** `~/.ssh/id_rsa_*` mit Berechtigungen `600`
- **Public Keys:** Können geteilt werden, müssen auf Server in `authorized_keys`
- **SSH-Config:** Sollte für Backup auch auf mehreren Systemen vorliegen

---

**Letztes Update:** 2026-01-14
