# Contributing to STM EU Tax Rate Sync

Thank you for considering contributing! 🎉 This extension is maintained by [Storetown-Media](https://www.storetown-media.de/) as an open-source contribution to the Magento ecosystem.

## How to contribute

### Reporting bugs
Use the [Bug Report template](.github/ISSUE_TEMPLATE/bug_report.md). Include version numbers and relevant log lines.

### Suggesting features
Use the [Feature Request template](.github/ISSUE_TEMPLATE/feature_request.md). Describe the use case first — *what* you want to solve, then *how*.

### Submitting code (Pull Requests)

1. **Before larger changes:** open an issue first so we can align on direction. Avoids wasted work.
2. **Fork** the repository, create a branch from `main`:
   ```bash
   git checkout -b feat/<short-description>
   ```
3. **Code style:** PSR-12 + Magento-2 Coding Standards
   ```bash
   vendor/bin/phpcs --standard=Magento2 app/code/Storetown/TaxSync
   ```
4. **Tests:** if you add a new feature or fix a bug, ideally add a test
5. **Commit messages:** [Conventional Commits](https://www.conventionalcommits.org/) preferred — e.g. `feat: add support for Norway VAT`, `fix: handle empty API response`
6. **Open a PR** against `main` with a clear description: what changed, why, and how to verify

### Security vulnerabilities

⚠️ **Do NOT open public issues for security problems.**
Email **security@storetown-media.de** — see [SECURITY.md](SECURITY.md).

## Development setup

```bash
# 1. Clone your fork
git clone https://github.com/<your-username>/stm-magento-2-eu-tax-rate-sync.git
cd stm-magento-2-eu-tax-rate-sync

# 2. Symlink into a local Magento installation
ln -s "$(pwd)" /path/to/magento/app/code/Storetown/TaxSync

# 3. Enable and run setup
cd /path/to/magento
bin/magento module:enable Storetown_TaxSync
bin/magento setup:upgrade
bin/magento cache:flush

# 4. Run CLI commands to test
bin/magento tax:sync:run
```

## Code of Conduct

Be kind. Be patient. Be specific. Be welcoming to newcomers.

Issues and PRs that contain insults, harassment or aggressive language will be closed without discussion.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
