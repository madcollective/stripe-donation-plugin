# This Dockerfile and accompanying setup scripts are curtesy of Cully Larson (@cullylarson).
FROM hgezim/wp-nocache

# Set timezone
RUN echo "America/Los_Angeles" | tee /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata

RUN apt-get update

# Pre-configure postfix
RUN echo "postfix postfix/mailname string localhost" | debconf-set-selections
RUN echo "postfix postfix/main_mailer_type string 'Internet Site'" | debconf-set-selections

RUN apt-get install -y postfix postfix-pcre mutt vim unzip git

# Set some php.ini stuff
RUN echo ""                         >> /usr/local/etc/php/conf.d/z-overwriting-config.ini
# opcache seems to cause some bugs in my programs, yo
RUN echo "opcache.enable=0"         >> /usr/local/etc/php/conf.d/z-overwriting-config.ini
RUN echo "opcache.enable_cli=0"     >> /usr/local/etc/php/conf.d/z-overwriting-config.ini

# Postfix (Catches all outgoing email, puts it in the 'root' user's mailbox. Use mutt to read it.)
RUN echo ""                                                                 >> /etc/postfix/main.cf
RUN echo "virtual_alias_domains ="                                          >> /etc/postfix/main.cf
RUN echo "virtual_alias_maps = pcre:/etc/postfix/virtual_forwardings.pcre"  >> /etc/postfix/main.cf
RUN echo "virtual_mailbox_domains = pcre:/etc/postfix/virtual_domains.pcre" >> /etc/postfix/main.cf
RUN echo "home_mailbox = Maildir/"                                          >> /etc/postfix/main.cf

# this defines where to forward the email
RUN echo "/@.*/ root"   > /etc/postfix/virtual_forwardings.pcre
RUN echo "/^.*/ OK"     > /etc/postfix/virtual_domains.pcre

# Mutt (for reading email)
RUN echo ""                                                                                                 >> /etc/Muttrc
RUN echo "set mbox_type=Maildir"                                                                            >> /etc/Muttrc
RUN echo "set folder=\"~/Maildir\""                                                                         >> /etc/Muttrc
RUN echo "set mask=\"!^\\.[^.]\""                                                                           >> /etc/Muttrc
RUN echo "set mbox=\"~/Maildir\""                                                                           >> /etc/Muttrc
RUN echo "set record=\"+.Sent\""                                                                            >> /etc/Muttrc
RUN echo "set postponed=\"+.Drafts\""                                                                       >> /etc/Muttrc
RUN echo "set spoolfile=\"~/Maildir\""                                                                      >> /etc/Muttrc
RUN echo "mailboxes \`echo -n \"+ \"; find ~/Maildir -maxdepth 1 -type d -name \".*\" -printf \"+'%f' \"\`"  >> /etc/Muttrc
RUN echo "macro index c \"<change-folder>?<toggle-mailboxes>\" \"open a different folder\""                 >> /etc/Muttrc
RUN echo "macro pager c \"<change-folder>?<toggle-mailboxes>\" \"open a different folder\""                 >> /etc/Muttrc

# Set TERM for root so can read mail using mutt
RUN echo "export TERM=xterm" >> /root/.bashrc

# wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# composer
RUN curl -O https://getcomposer.org/composer.phar \
    && chmod +x composer.phar \
    && mv composer.phar /usr/local/bin/composer

# Need to set the shell for the www-data user, so we can run setup commands as that user later
RUN chsh -s /bin/bash www-data
