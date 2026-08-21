#!/bin/bash
# Run as root on the server (CyberPanel / OpenLiteSpeed).
# Creates poshdev.isarva.in vhost matching crmdev.isarva.in.

set -euo pipefail

POSH_ROOT=/home/hrmsdev.isarva.in/public_html/posh
CRM_VHOST=/usr/local/lsws/conf/vhosts/crmdev.isarva.in
POSH_VHOST=/usr/local/lsws/conf/vhosts/poshdev.isarva.in
POSH_CACHE=/usr/local/lsws/cachedata/poshdev.isarva.in

if [[ $EUID -ne 0 ]]; then
    echo "Run as root: sudo bash $0" >&2
    exit 1
fi

if [[ ! -d "$CRM_VHOST" ]]; then
    echo "Reference vhost missing: $CRM_VHOST" >&2
    exit 1
fi

if [[ -d "$POSH_VHOST" ]]; then
    echo "Updating existing poshdev vhost"
else
    echo "Creating poshdev vhost from crmdev template"
    cp -a "$CRM_VHOST" "$POSH_VHOST"
fi

sed -i \
    -e 's/crmdev\.isarva\.in/poshdev.isarva.in/g' \
    -e 's|public_html/crm|public_html/posh|g' \
    "$POSH_VHOST/vhconf.conf"

mkdir -p "$POSH_CACHE"
chown -R lsadm:lsadm "$POSH_VHOST" "$POSH_CACHE" 2>/dev/null || true

/usr/local/lsws/bin/lswsctrl restart

echo "Done. Document root should be: ${POSH_ROOT}"
echo "Test: curl -sI https://poshdev.isarva.in/ | head -5"
