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

# Create HTML diff of samples
# Requires diff that supports -U context option
# Requires python package pygmentize (easy_install pygments)
diff -U9999999 sample_before.php sample_after.php | pygmentize -l diff -f html -O full -o sample_diff.html
