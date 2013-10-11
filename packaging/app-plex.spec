
Name: app-plex
Epoch: 1
Version: 1.0.0
Release: 1%{dist}
Summary: Plex Media Server
License: GPLv3
Group: ClearOS/Apps
Packager: eLogic
Vendor: eLogic
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
The Plex Media Server is the backend application to help you manage and stream media to almost any network connected device.

%package core
Summary: Plex Media Server - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: plexmediaserver

%description core
The Plex Media Server is the backend application to help you manage and stream media to almost any network connected device.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/plex
cp -r * %{buildroot}/usr/clearos/apps/plex/

install -D -m 0644 packaging/plexmediaserver.php %{buildroot}/var/clearos/base/daemon/plexmediaserver.php

%post
logger -p local6.notice -t installer 'app-plex - installing'

%post core
logger -p local6.notice -t installer 'app-plex-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/plex/deploy/install ] && /usr/clearos/apps/plex/deploy/install
fi

[ -x /usr/clearos/apps/plex/deploy/upgrade ] && /usr/clearos/apps/plex/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-plex - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-plex-core - uninstalling'
    [ -x /usr/clearos/apps/plex/deploy/uninstall ] && /usr/clearos/apps/plex/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/plex/controllers
/usr/clearos/apps/plex/htdocs
/usr/clearos/apps/plex/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/plex/packaging
%exclude /usr/clearos/apps/plex/tests
%dir /usr/clearos/apps/plex
/usr/clearos/apps/plex/deploy
/usr/clearos/apps/plex/language
/usr/clearos/apps/plex/libraries
/var/clearos/base/daemon/plexmediaserver.php
