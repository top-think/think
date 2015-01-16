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
#include "xxtea.h"

void xxtea_long_encrypt(xxtea_long *v, xxtea_long len, xxtea_long *k) {
    xxtea_long n = len - 1;
    xxtea_long z = v[n], y = v[0], p, q = 6 + 52 / (n + 1), sum = 0, e;
    if (n < 1) {
        return;
    }
    while (0 < q--) {
        sum += XXTEA_DELTA;
        e = sum >> 2 & 3;
        for (p = 0; p < n; p++) {
            y = v[p + 1];
            z = v[p] += XXTEA_MX;
        }
        y = v[0];
        z = v[n] += XXTEA_MX;
    }
}

void xxtea_long_decrypt(xxtea_long *v, xxtea_long len, xxtea_long *k) {
    xxtea_long n = len - 1;
    xxtea_long z = v[n], y = v[0], p, q = 6 + 52 / (n + 1), sum = q * XXTEA_DELTA, e;
    if (n < 1) {
        return;
    }
    while (sum != 0) {
        e = sum >> 2 & 3;
        for (p = n; p > 0; p--) {
            z = v[p - 1];
            y = v[p] -= XXTEA_MX;
        }
        z = v[n];
        y = v[0] -= XXTEA_MX;
        sum -= XXTEA_DELTA;
    }
}
