#!/bin/sh
# Make HTML documentation
# Requires phpDocumeter 1.4 and PHP 5.x with tokenizer support
phpdoc \
 --output HTML:frames:earthli \
 --directory confidentcaptcha \
 --target docs \
 --title "Confident CAPTCHA PHP Library" \
 --ignore captchalib.php \
 --sourcecode on \
 --undocumentedelements on 
