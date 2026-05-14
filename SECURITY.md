# Security Policy

## Reporting a vulnerability

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, email **security@storetown-media.de** with:

- A description of the vulnerability
- Steps to reproduce (or a proof-of-concept)
- Your assessment of impact (e.g. RCE, SQL injection, XSS, auth bypass, data exposure)
- Suggested fix if you have one

We aim to:

- **Acknowledge** receipt within 2 business days
- **Provide a status update** within 7 business days
- **Release a fix** within 30 days for critical issues (sooner if exploit is in the wild)

You can encrypt your email using our PGP key (available on request).

## Disclosure policy

- We follow **coordinated disclosure**. We will work with you on a timeline.
- Once a fix is released, we credit the reporter in the [release notes](../../releases) unless you prefer to remain anonymous.
- After a fix is released and most users have had time to update (typically 30 days), full details may be published.

## Supported versions

Only the **latest minor release** of each supported major version receives security patches.

| Version | Supported |
|---|---|
| 1.x.x   | ✅ Yes — active |
| < 1.0.0 | ❌ No  — please upgrade |

## Out of scope

The following are NOT considered security vulnerabilities of this extension:

- Vulnerabilities in third-party Magento modules
- Vulnerabilities in Magento core (please report to [Adobe Security](https://helpx.adobe.com/security/products/magento.html))
- Vulnerabilities in PHP, MySQL, web server stack
- Missing security headers in your Magento installation (configure your web server)
- Social-engineering attacks against shop operators

## Thanks

We deeply appreciate responsible security research. Thank you for helping keep the Magento ecosystem secure.
