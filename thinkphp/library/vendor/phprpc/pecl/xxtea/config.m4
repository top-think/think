PHP_ARG_ENABLE(xxtea, xxtea module,
[  --enable-xxtea          Enable xxtea module.])

if test "$PHP_XXTEA" != "no"; then
  PHP_NEW_EXTENSION(xxtea, php_xxtea.c xxtea.c, $ext_shared)
  AC_DEFINE(HAVE_XXTEA, 1, [Have XXTEA library])
fi
