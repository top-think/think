/***********************************************************************

    Copyright 2006-2007 Ma Bingyao

    These sources is free software. Redistributions of source code must
    retain the above copyright notice. Redistributions in binary form
    must reproduce the above copyright notice. You can redistribute it
    freely. You can use it with any free or commercial software.

    These sources is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY. Without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

        You may contact the author by:
           e-mail:  andot@coolcode.cn

*************************************************************************/
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"

#if HAVE_XXTEA
#include "php_xxtea.h"
#include "ext/standard/info.h" /* for phpinfo() functions */
#include "xxtea.h"

/* compiled function list so Zend knows what's in this module */
zend_function_entry xxtea_functions[] =
{
    ZEND_FE(xxtea_encrypt, NULL)
    ZEND_FE(xxtea_decrypt, NULL)
    ZEND_FE(xxtea_info, NULL)
    {NULL, NULL, NULL}
};

/* compiled module information */
zend_module_entry xxtea_module_entry =
{
    STANDARD_MODULE_HEADER,
    XXTEA_MODULE_NAME,
    xxtea_functions,
    ZEND_MINIT(xxtea),
    ZEND_MSHUTDOWN(xxtea),
    NULL,
    NULL,
    ZEND_MINFO(xxtea),
    XXTEA_VERSION,
    STANDARD_MODULE_PROPERTIES
};

/* implement standard "stub" routine to introduce ourselves to Zend */
#if defined(COMPILE_DL_XXTEA)
ZEND_GET_MODULE(xxtea)
#endif

static xxtea_long *xxtea_to_long_array(unsigned char *data, xxtea_long len, int include_length, xxtea_long *ret_len) {
    xxtea_long i, n, *result;
	n = len >> 2;
    n = (((len & 3) == 0) ? n : n + 1);
    if (include_length) {
        result = (xxtea_long *)emalloc((n + 1) << 2);
        result[n] = len;
	    *ret_len = n + 1;
	} else {
        result = (xxtea_long *)emalloc(n << 2);
	    *ret_len = n;
    }
	memset(result, 0, n << 2);
	for (i = 0; i < len; i++) {
        result[i >> 2] |= (xxtea_long)data[i] << ((i & 3) << 3);
    }
    return result;
}

static unsigned char *xxtea_to_byte_array(xxtea_long *data, xxtea_long len, int include_length, xxtea_long *ret_len) {
    xxtea_long i, n, m;
    unsigned char *result;
    n = len << 2;
    if (include_length) {
        m = data[len - 1];
        if ((m < n - 7) || (m > n - 4)) return NULL;
        n = m;
    }
    result = (unsigned char *)emalloc(n + 1);
	for (i = 0; i < n; i++) {
        result[i] = (unsigned char)((data[i >> 2] >> ((i & 3) << 3)) & 0xff);
    }
	result[n] = '\0';
	*ret_len = n;
	return result;
}

static unsigned char *php_xxtea_encrypt(unsigned char *data, xxtea_long len, unsigned char *key, xxtea_long *ret_len) {
    unsigned char *result;
    xxtea_long *v, *k, v_len, k_len;
    v = xxtea_to_long_array(data, len, 1, &v_len);
    k = xxtea_to_long_array(key, 16, 0, &k_len);
    xxtea_long_encrypt(v, v_len, k);
    result = xxtea_to_byte_array(v, v_len, 0, ret_len);
    efree(v);
    efree(k);
    return result;
}

static unsigned char *php_xxtea_decrypt(unsigned char *data, xxtea_long len, unsigned char *key, xxtea_long *ret_len) {
    unsigned char *result;
    xxtea_long *v, *k, v_len, k_len;
    v = xxtea_to_long_array(data, len, 0, &v_len);
    k = xxtea_to_long_array(key, 16, 0, &k_len);
    xxtea_long_decrypt(v, v_len, k);
    result = xxtea_to_byte_array(v, v_len, 1, ret_len);
    efree(v);
    efree(k);
    return result;
}

/* {{{ proto string xxtea_encrypt(string data, string key)
   Encrypt string using XXTEA algorithm */
ZEND_FUNCTION(xxtea_encrypt)
{
    unsigned char *data, *key;
    unsigned char *result;
    xxtea_long data_len, key_len, ret_length;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &data, &data_len, &key, &key_len) == FAILURE) {
        return;
    }
	if (data_len == 0) RETVAL_STRINGL(NULL, 0, 0);
    if (key_len != 16) RETURN_FALSE;
    result = php_xxtea_encrypt(data, data_len, key, &ret_length);
    if (result != NULL) {
        RETVAL_STRINGL((char *)result, ret_length, 0);
    } else {
        RETURN_FALSE;
    }
}
/* }}} */


/* {{{ proto string xxtea_decrypt(string data, string key)
   Decrypt string using XXTEA algorithm */
ZEND_FUNCTION(xxtea_decrypt)
{
    unsigned char *data, *key;
    unsigned char *result;
    xxtea_long data_len, key_len, ret_length;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &data, &data_len, &key, &key_len) == FAILURE) {
        return;
    }
	if (data_len == 0) RETVAL_STRINGL(NULL, 0, 0);
    if (key_len != 16) RETURN_FALSE;
    result = php_xxtea_decrypt(data, data_len, key, &ret_length);
    if (result != NULL) {
		RETVAL_STRINGL((char *)result, ret_length, 0);
    } else {
        RETURN_FALSE;
    }
}
/* }}} */

ZEND_MINIT_FUNCTION(xxtea)
{
    return SUCCESS;
}

ZEND_MSHUTDOWN_FUNCTION(xxtea)
{
    return SUCCESS;
}

ZEND_MINFO_FUNCTION(xxtea)
{
    php_info_print_table_start();
    php_info_print_table_row(2, "xxtea support", "enabled");
    php_info_print_table_row(2, "xxtea module version", XXTEA_VERSION);
	php_info_print_table_row(2, "xxtea author", XXTEA_AUTHOR);
    php_info_print_table_row(2, "xxtea homepage", XXTEA_HOMEPAGE);
	php_info_print_table_end();
}

ZEND_FUNCTION(xxtea_info)
{
    array_init(return_value);
    add_assoc_string(return_value, "ext_version", XXTEA_VERSION, 1);
    add_assoc_string(return_value, "ext_build_date", XXTEA_BUILD_DATE, 1);
	add_assoc_string(return_value, "ext_author", XXTEA_AUTHOR, 1);
    add_assoc_string(return_value, "ext_homepage", XXTEA_HOMEPAGE, 1);
}

#endif /* if HAVE_XXTEA */
