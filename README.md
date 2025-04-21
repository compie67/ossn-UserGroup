WARNING!!!!! stand alone this works, but when using the module GroepSlugRouter remove this module

# UserGroups Component for OSSN


This component displays an overview of the groups a user manages on their public profile page.

Example URL:  
https://shadow.nlsociaal.nl/u/username/groups?sort=members


---

## 🧩 Features

- Displays group cover photos (if available)  
- Shows member count for each group  
- Integrates with OSSN's profile subpage structure  
- Supports sorting:
  - Newest first (`?sort=newest`)
  - Oldest first (`?sort=oldest`)
  - Most members (`?sort=members`)
8-4-2025 UPDATE add sorting AtoZ and ZtoA
- Responsive design — mobile-friendly  
- Fully multilingual (Dutch and English supported)
- 
8-4-2025 feebvack from LiangLee
** Fixed Translations ossn_print strings
** Show group cover instead
** Show dummy group cover. (royality free image)
** Fixed group privacy
** Remove html entity decode as it can result int XSS attack
---

## 📦 Installation

1. Copy the `UserGroups` folder into your OSSN `/components` directory  
2. Go to the OSSN admin panel and activate the component  
3. Make sure the user is a member or owner of groups — otherwise, nothing will be displayed

---

## 💶 Support this project

This component is released under the [GNU General Public License v2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

> Do you like what we're building? Consider supporting our work:

👉 [https://nlsociaal.nl/p/2248/steun-ons](https://nlsociaal.nl/p/2248/steun-ons) and make a choice off your favorite donation platform

Any form of support is deeply appreciated ❤️

---

## 👨‍💻 Authors

- **Eric Redegeld** — [nlsociaal.nl](https://nlsociaal.nl)  
- **ChatGPT/Copilot** — assistance, code review & documentation
