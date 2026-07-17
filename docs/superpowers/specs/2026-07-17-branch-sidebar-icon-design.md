# Branch Sidebar Route Icon Design

**Goal:** Show the supplied `solar_route-outline.svg` icon on the right side of every branch card in the school-map sidebar.

**Scope:** Copy the SVG into the theme assets, expose its URL through the existing localized map config, append a decorative image during branch-card rendering, and style its size and alignment. The API, branch data, click behavior, and accessibility semantics of the card remain unchanged.

**Approved approach:** Use an ordinary `<img>` with empty alt text and `aria-hidden="true"`; keep the existing `<li>` as the keyboard-focusable/clickable surface. This preserves the supplied SVG exactly and avoids adding a new content field.

**Verification:** Open the local homepage, select Дніпропетровська область → Дніпро, and confirm the route icon appears on every branch card without changing the branch label or address.
