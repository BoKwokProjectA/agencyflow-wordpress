# 02 — Content to enter

Nine fictional projects, spread across the four project types so the filter has
something meaningful to do. Copy each block into **Projects → Add New**.

Fields per project: Title, Content (main editor), Excerpt, Project Type
(taxonomy checkbox), plus the four **Project Details** fields.

Grab landscape photos from `unsplash.com` for the featured images. Roughly
8:5 works best with the card layout.

---

## 1. Northern Roasters — brand site rebuild

**Type:** Website
**Client:** Northern Roasters
**Technologies:** WordPress, PHP, JavaScript, CSS Grid
**Completion date:** 2025-11-18
**Project URL:** https://example.com/northern-roasters

**Excerpt:**
A speciality coffee roaster's brochure site rebuilt as a custom WordPress theme,
cutting page weight by two thirds.

**Content:**
Northern Roasters came to us on a heavily plugin-dependent site that took over
six seconds to load on mobile. We rebuilt it as a custom theme with no page
builder, hand-written CSS and only the JavaScript the site actually needs.

The team wanted to manage their own seasonal coffee listings, so we registered a
custom content type for them with a small set of clearly labelled fields rather
than a wall of generic custom fields. Training took twenty minutes.

Page weight dropped from 4.1MB to 1.3MB and the mobile Lighthouse performance
score went from 34 to 91.

---

## 2. Peak Outfitters — online store

**Type:** E-commerce
**Client:** Peak Outfitters
**Technologies:** WordPress, WooCommerce, PHP, JavaScript
**Completion date:** 2026-02-06
**Project URL:** https://example.com/peak-outfitters

**Excerpt:**
An outdoor equipment retailer moved from a market-stall spreadsheet to a
440-product online store with click-and-collect.

**Content:**
Peak Outfitters had been running stock on a shared spreadsheet and taking orders
over the phone. The brief was a proper store that their two-person team could
actually keep up with.

We built a custom product import so their supplier's CSV feed could be brought
in without retyping anything, and added a click-and-collect option that checks
stock at the Glossop shop before offering the choice at checkout.

The first quarter after launch took 38% of revenue online, with no increase in
staff hours.

---

## 3. Mersey Legal — enquiry triage automation

**Type:** Automation
**Client:** Mersey Legal
**Technologies:** PHP, REST API, JavaScript, Webhooks
**Completion date:** 2026-01-22
**Project URL:** https://example.com/mersey-legal

**Excerpt:**
Website enquiries now route themselves to the right department and confirm to the
sender in under a second.

**Content:**
A three-office law firm was manually forwarding every website enquiry from one
shared inbox, and enquiries were regularly sitting unread for two days.

We replaced the plain contact form with one that captures the matter type, then
validates and stores each enquiry, notifies the correct department, and sends the
sender an immediate acknowledgement with a reference number.

Average first response went from 31 hours to under 2 hours. Nobody on the team
had to change how they work.

---

## 4. Bramhall Dental — appointment portal

**Type:** Web Application
**Client:** Bramhall Dental
**Technologies:** PHP, MySQL, JavaScript, REST API
**Completion date:** 2025-09-30
**Project URL:** https://example.com/bramhall-dental

**Excerpt:**
A patient-facing booking portal that reads live availability from the practice's
existing scheduling system.

**Content:**
The practice already had scheduling software they were happy with, so replacing
it was off the table. The problem was that patients could only book by phone,
during the hours the phone was staffed.

We built a booking portal that reads availability from the existing system's API
and writes confirmed appointments back to it, so reception sees bookings in the
software they already use.

Around a third of appointments are now booked outside office hours.

---

## 5. Salford Community Trust — grant microsite

**Type:** Website
**Client:** Salford Community Trust
**Technologies:** WordPress, PHP, CSS Flexbox, JavaScript
**Completion date:** 2025-07-14
**Project URL:** https://example.com/salford-trust

**Excerpt:**
An accessible microsite for a grant programme, built to WCAG 2.2 AA and
delivered in three weeks.

**Content:**
The Trust needed a standalone site for a new grant round, with a hard deadline
set by the funder. Accessibility was a formal requirement of the funding rather
than an aspiration.

We built it as a small custom theme with semantic markup throughout, a visible
focus style on every interactive element, and a full keyboard path through the
eligibility checker. It was tested with a screen reader before launch.

The site passed the funder's independent accessibility audit with no remedial
actions.

---

## 6. Hattersley Joinery — quote calculator

**Type:** Web Application
**Client:** Hattersley Joinery
**Technologies:** JavaScript, PHP, WordPress, REST API
**Completion date:** 2026-03-11
**Project URL:** https://example.com/hattersley

**Excerpt:**
An instant quote calculator for bespoke joinery, replacing a fortnight-long
manual estimating process.

**Content:**
Every enquiry previously needed a site visit before a price could be given,
which meant the workshop was quoting for jobs it never won.

We built a calculator that prices standard configurations from a rules table the
workshop manager maintains himself. Anything outside those rules is flagged for a
proper site visit instead of guessing.

Roughly 60% of enquiries now self-serve a price, and site visits are spent on
jobs far more likely to convert.

---

## 7. Didsbury Kitchens — stock sync

**Type:** Automation
**Client:** Didsbury Kitchens
**Technologies:** PHP, REST API, JSON, Cron
**Completion date:** 2025-12-04
**Project URL:** https://example.com/didsbury-kitchens

**Excerpt:**
Supplier stock levels now sync to the website four times a day instead of once a
week by hand.

**Content:**
The showroom was advertising appliances that had been discontinued weeks earlier,
because updating the site meant someone copying figures out of a supplier portal.

We built a scheduled job that pulls the supplier's JSON feed, compares it with
what the site holds, and updates only what changed — logging every run so a
failed sync is visible rather than silent.

Customer complaints about unavailable stock stopped within the first month.

---

## 8. Chorlton Bookshop — online orders

**Type:** E-commerce
**Client:** Chorlton Bookshop
**Technologies:** WordPress, WooCommerce, JavaScript, CSS Grid
**Completion date:** 2025-10-09
**Project URL:** https://example.com/chorlton-bookshop

**Excerpt:**
An independent bookshop's first online storefront, with local delivery by
postcode.

**Content:**
An independent shop with a strong local following and no way to sell outside
opening hours.

The catalogue is large and changes constantly, so rather than maintaining product
pages by hand we built a search-first storefront over their wholesaler's feed,
with a curated staff-picks section the booksellers control themselves.

Local delivery is offered automatically for a list of postcodes the shop
maintains, with collection everywhere else.

---

## 9. Ancoats Fitness — member dashboard

**Type:** Web Application
**Client:** Ancoats Fitness
**Technologies:** PHP, MySQL, JavaScript, REST API
**Completion date:** 2026-04-28
**Project URL:** https://example.com/ancoats-fitness

**Excerpt:**
A member dashboard for class booking and attendance, replacing a paper sign-in
sheet.

**Content:**
Two studios, a paper sign-in sheet, and no reliable way to know which classes
were worth keeping on the timetable.

We built a member dashboard where classes can be booked and cancelled, with a
waiting list that promotes automatically when someone drops out. Staff get an
attendance view per class and per instructor.

The timetable was reworked after the first eight weeks of real attendance data,
and average class occupancy rose from 61% to 84%.

---

## Type distribution check

| Type | Projects |
|---|---|
| Website | 1, 5 |
| E-commerce | 2, 8 |
| Automation | 3, 7 |
| Web Application | 4, 6, 9 |

Every filter button returns at least two results, and no filter returns
everything — which is what makes the feature visibly work when you demo it.
