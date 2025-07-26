# Media Wipe - Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.2] - 2025-07-26

### Added
- **Dashboard Tabbed Navigation**: Replaced broken Quick Navigation with modern tabbed navigation system from Support page
  - Added Overview, Quick Actions, Statistics, Recent Activity, and System Info tabs
  - Implemented smooth tab switching with JavaScript
  - Enhanced dashboard organization and user experience

- **Plugin Notice Suppression**: Implemented system to hide notices from other plugins on Media Wipe pages
  - Clean interface with only relevant Media Wipe notices displayed
  - Improved focus and reduced visual clutter

- **Release Date Display**: Added release date information to Support page plugin information section
  - Shows current release date: July 26, 2025
  - Enhanced plugin information transparency

### Fixed
- **Dashboard URL Routing**: Fixed URL mismatch between main dashboard and redirect targets
  - Updated redirect functions to point to correct `admin.php?page=media-wipe` URL
  - Resolved navigation inconsistencies throughout the plugin

- **Support Page Styling**: Removed left border color from featured feature cards
  - Cleaner appearance on support page
  - Improved visual consistency across all cards

### Improved
- **Settings Page Design**: Enhanced settings page with modern aesthetic
  - Improved form layout and styling
  - Better organization of settings sections
  - Consistent design language with rest of plugin

- **Dashboard User Experience**: Complete overhaul of dashboard navigation and content organization
  - Tabbed interface for better content discovery
  - Organized sections for different types of information
  - Improved accessibility and usability

### Technical
- **Code Organization**: Improved plugin structure and maintainability
- **CSS Consistency**: Standardized styling across all plugin pages
- **JavaScript Enhancement**: Added interactive tab functionality for better UX

---

## [1.2.1] - Previous Release

### Added
- AI-powered unused media detection with confidence scoring
- Advanced scanning algorithms for better accuracy
- Enhanced safety features and user guidance
- Comprehensive audit logging system

### Fixed
- AJAX action conflicts between different deletion methods
- DataTables integration issues with bulk operations
- Console logging appearing in production environments
- Unused media detection algorithm reliability

### Improved
- Modern dashboard design with gradient backgrounds
- Collapsible safety banners with persistent state
- Enhanced statistics display with badge-style percentages
- Mobile-responsive design improvements

---

## Previous Versions

For detailed information about earlier versions, please refer to the documentation in the `/docs` folder:
- `RELEASE_NOTES_v1.0.4.md`
- `IMPLEMENTATION_SUMMARY_v1.1.1.md`
- `PRD_v1.1.1.md`

---

## Support

For support, feature requests, or bug reports, please visit:
- **Author**: Md. Nahid Hasan
- **Website**: [mdnahidhasan.netlify.app](https://mdnahidhasan.netlify.app)
- **Plugin URI**: [Media Wipe Plugin Page](https://mdnahidhasan.netlify.app/media-wipe)

---

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.
