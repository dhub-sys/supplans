# Support Plans 1.0.7 — Joomla 6

- Διαχωρίζει τα **Πλάνα** (Grey, Green, Ultra κ.λπ.) από τα **Πακέτα πελατών**.
- Νέο Πακέτο: δημιουργεί αγορά πελάτη από επιλεγμένο πλάνο.
- Νέο Πλάνο: δημιουργεί νέα υπηρεσία με ώρες, συνολικό κόστος, ελάχιστη χρέωση, step χρόνου και περιγραφή.
- Τα πλάνα μπορούν να επεξεργαστούν και να απενεργοποιηθούν.
- Οι όροι αντιγράφονται ως snapshot στο πακέτο κατά την αγορά ώστε μελλοντικές αλλαγές τιμών να μην αλλάζουν παλιές αγορές.
- Η χρέωση εργασίας χρησιμοποιεί το min/step του συγκεκριμένου πακέτου.


## v1.0.8

- Το κουμπί αναφοράς μετονομάστηκε σε «Εκτύπωση / PDF».
- Η αναφορά προφορτώνεται σε κρυφό iframe μέσα στην οθόνη του πακέτου.
- Με το πάτημα του κουμπιού ανοίγει απευθείας το native print dialog του browser.
- Δεν ανοίγει νέο tab, νέο παράθυρο ή ενδιάμεσο popup.
- Μετά το κλείσιμο του print dialog ο διαχειριστής παραμένει στο ίδιο πακέτο.


## v1.0.9

- Οι «Πληροφορίες πλάνου υποστήριξης» μεταφέρθηκαν στην 1η σελίδα της αναφοράς.
- Κάτω από την πράσινη επικεφαλίδα εμφανίζεται απευθείας η περιγραφή του επιλεγμένου πλάνου, χωρίς ξεχωριστό τίτλο «Περιγραφή πλάνου υποστήριξης».
- Κάτω από την περιγραφή εμφανίζονται η ημερομηνία αγοράς και τα υποστηριζόμενα websites.
- Η 2η σελίδα ξεκινά πλέον απευθείας με το «Αναλυτικό δελτίο εργασιών».
- Στις γραμμές «Αρ. εντύπου», «Ημερομηνία έκδοσης» και «Πελάτης» είναι bold μόνο οι τίτλοι αριστερά και όχι οι τιμές δεξιά.


## v1.0.10

- Προστέθηκε «Σύνοψη πλάνου» στην 1η σελίδα του PDF, κάτω από τις πληροφορίες πλάνου.
- Η σύνοψη έχει τρία διακριτά κουτιά: Υπόλοιπο χρόνου, Διαθέσιμο ποσό, Χρησιμοποιήθηκαν.
- Αυξήθηκαν επιλεκτικά τα κενά μεταξύ των τριών βασικών ενοτήτων της 1ης σελίδας, χωρίς αλλαγή της συνολικής αισθητικής του report.


## v1.0.11

- Στην 1η σελίδα της αναφοράς το λογότυπο μεταφέρθηκε επάνω αριστερά.
- Τα εταιρικά στοιχεία της Dazzlink μεταφέρθηκαν επάνω δεξιά.
- Η επικεφαλίδα «Σύνοψη πλάνου» έγινε πράσινη μπάρα, ίδιας αισθητικής με τις «Πληροφορίες πλάνου υποστήριξης».
- Τα τρία πεδία της σύνοψης χρησιμοποιούν καθαρά inline SVG icons (ρολόι, wallet, κλεψύδρα), χωρίς εξωτερικές βιβλιοθήκες.
- Διατηρήθηκε η υπόλοιπη δομή του PDF.


## v1.0.12

- Προστέθηκε περισσότερο κατακόρυφο κενό μεταξύ logo/εταιρικών στοιχείων και τίτλου του αναλυτικού δελτίου.
- Προστέθηκε περισσότερο κενό πριν από τις «Πληροφορίες πλάνου υποστήριξης».
- Προστέθηκε περισσότερο κενό πριν από τη «Σύνοψη πλάνου».
- Δεν άλλαξε η υπόλοιπη δομή ή αισθητική της αναφοράς.


## v1.0.14 — White Label
- Προστέθηκαν Joomla Options / Branding.
- Ρυθμιζόμενα: επωνυμία, λογότυπο, διεύθυνση, τηλέφωνο, email, website, accent color, τίτλος report και footer.
- Το PDF/report διαβάζει αποκλειστικά τις ρυθμίσεις branding.
- Αν δεν οριστούν νέες τιμές, διατηρούνται τα υπάρχοντα Dazzlink defaults για συμβατότητα.
- Δεν αλλάζει κανένα υπάρχον πακέτο, πλάνο ή εργασία.


## v1.0.15 Standalone White Label
- Πλήρες standalone installer για καθαρή εγκατάσταση σε Joomla 6 χωρίς προηγούμενη έκδοση.
- Ο installer δημιουργεί μόνος του όλους τους απαιτούμενους πίνακες.
- Παραμένει συμβατό και ως update σε παλιότερη εγκατάσταση.
- Δεν υπάρχει ενεργό Dazzlink branding ως default.
- Αν δεν οριστεί επωνυμία, χρησιμοποιείται το Site Name του Joomla.
- Αν δεν οριστεί λογότυπο, το report λειτουργεί χωρίς λογότυπο.
- Branding, στοιχεία εταιρείας, accent, τίτλος και footer ρυθμίζονται από Options.


## v1.0.16
Ongoing / Pay as you go plans with configurable hourly rate, minimum charge and billing step. Ongoing customer plans accumulate actual time, billable time and cost. White-label business details remain configurable in Joomla component Options.


## v1.0.17
- Διορθώθηκε το μήνυμα «This component has no configuration options».
- Τα white-label στοιχεία δεν εξαρτώνται πλέον από το Joomla com_config.
- Προστέθηκε αυτόνομη σελίδα Support Plans → Στοιχεία επιχείρησης.
- Αποθηκεύει επωνυμία, logo path, διεύθυνση, τηλέφωνο, email, website, branding color, report title και footer στα component params.
- Το report/PDF συνεχίζει να διαβάζει τα ίδια component params.


## v1.0.18 — Συνδρομητικό πλάνο
- Τρίτος τύπος χρέωσης: Συνδρομητικό.
- Ρυθμιζόμενη διάρκεια σε μήνες και σταθερή αξία συνδρομής.
- Default Subscription: 1 μήνας / 300,00 € προ ΦΠΑ.
- Οι εργασίες καταγράφονται χωρίς ωριαία χρέωση και χωρίς χρεώσιμο χρόνο.
- Το PDF εμφανίζει τη σταθερή αξία προ ΦΠΑ και την αξία με ΦΠΑ.
- Οι χρηματικές ενδείξεις στα reports διευκρινίζουν ότι οι τιμές είναι προ ΦΠΑ.


## v1.0.19
- Πλήρης standalone installer για καθαρό Joomla 6 και update.
- Τίτλοι PDF ρυθμιζόμενοι ανά πλάνο: κύριος, πληροφορίες, εργασίες, footer.
- Δεύτερο branding χρώμα από «Στοιχεία επιχείρησης», οπτικά πάνω από το βασικό χρώμα στο PDF.
- Fallback στους γενικούς τίτλους όταν οι τίτλοι πλάνου είναι κενοί.


## v1.0.20 — Standalone UI
- «Standalone» πλέον σημαίνει καθαρό application window χωρίς Joomla administrator chrome.
- Το Joomla administrator menu έχει ένα μόνο top-level κουμπί «Support Plans».
- Το κουμπί ανοίγει το Support Plans σε νέο tab/window με `tmpl=component`.
- Η εσωτερική πλοήγηση Πακέτα / Πλάνα / Στοιχεία Επιχείρησης παραμένει μέσα στο standalone UI.
- Προστέθηκε «Επιστροφή στο Joomla».
- Τα redirects μετά από Save/Cancel/Delete διατηρούν το standalone `tmpl=component`.


## v1.0.21 — SaaS UI
- Ένα μόνο «Support Plans» entry στο Joomla Administrator sidebar.
- Ανοίγει σε νέο tab/window με καθαρό `tmpl=component`, χωρίς Joomla admin chrome.
- Νέο SaaS-style application header, workspace navigation, KPI cards και πιο καθαρά cards/tables/forms.
- Όλα τα internal links, Save/Cancel/Delete παραμένουν μέσα στο standalone workspace.
- Διατηρούνται prepaid, ongoing, subscription, white-label branding και PDF customization.


## v1.0.22 — First-level Administrator launcher
- Το Support Plans αφαιρέθηκε από το standard Components submenu.
- Εγκαθίσταται/ενεργοποιείται launcher που δημιουργεί πραγματικό level-1 Administrator menu item.
- Το πρώτο click ανοίγει απευθείας το SaaS UI σε νέο tab (`browserNav=1`) με `tmpl=component`.


## v1.0.23
- Διορθώθηκε το σφάλμα installer για τον φάκελο plugins: πλέον αντιγράφεται από το administrator section.
- Το SaaS περιβάλλον προσωποποιείται από επωνυμία, λογότυπο και τα δύο branding χρώματα.
- Αφαιρέθηκε το uppercase styling από τίτλους, labels πλοήγησης και κεφαλίδες πινάκων.
- Ο top-level launcher παραμένει έξω από Components και ανοίγει σε νέο tab.


## v1.0.24
- Διορθώθηκε ο launcher του Joomla Administrator.
- Χρησιμοποιεί πλέον το επίσημο `onPreprocessMenuItems` event του Joomla 6 για να προσθέτει το Support Plans απευθείας στο πρώτο επίπεδο του sidebar.
- Το link έχει `target="_blank"` και ανοίγει κατευθείαν το standalone SaaS interface.
- Ο launcher είναι σύγχρονο namespaced Joomla system plugin με `services/provider.php`.
- Καθαρίζεται το plugin cache μετά την εγκατάσταση/update.


## v1.0.25
- Ο top-level launcher δεν βασίζεται πλέον στο dynamic Joomla menu preset/event.
- Το system plugin προσθέτει ορατό «Support Plans» απευθείας στο πραγματικό Administrator sidebar μετά το render.
- Το κουμπί ανοίγει σε νέο tab με `tmpl=component&spapp=1`.
- Προστέθηκε και standard Components entry ως fallback, ώστε να υπάρχει πάντα τρόπος πρόσβασης.


## v1.0.26
- Το εικονίδιο και ο τίτλος «Support Plans» στο Joomla Administrator sidebar είναι λευκά.
- Το SaaS interface και το sidebar launcher χρησιμοποιούν Geom από Google Fonts.
- Υπάρχουν Arial/Helvetica fallbacks αν το Google Fonts δεν είναι διαθέσιμο.


## v1.0.27
- Το Joomla sidebar launcher δεν φορτώνει πλέον Geom και κληρονομεί τη γραμματοσειρά του Joomla Administrator.
- Αφαιρέθηκε το προηγούμενο διπλό/briefcase icon και χρησιμοποιείται ένα ενιαίο icon εγγράφου/δελτίου εργασιών.
- Το standalone διατηρεί Geom αλλά έχει νέο UX/UI: πιο καθαρό app bar, segmented navigation, πιο ήσυχα KPI cards, καλύτερα tables/forms, spacing, focus states και mobile συμπεριφορά.


## v1.0.28
- Πραγματικό αριστερό sidebar στο standalone SaaS interface.
- Μενού: Πακέτα, Πλάνα, Ρυθμίσεις με καθαρά line icons και active state.
- Σταθερό sidebar σε desktop, compact icon rail σε tablet και horizontal navigation σε mobile.
- Το κύριο workspace έχει πλέον ξεχωριστή περιοχή περιεχομένου και καλύτερη SaaS ιεραρχία.


## v1.0.29
- Standard blue/neutral SaaS palette στο standalone interface.
- Business branding colors χρησιμοποιούνται μόνο στις PDF/report αναφορές.
- Τα Grey και Green prepaid plans αφαιρέθηκαν από τα fresh-install seeds.
- Σε update δεν διαγράφονται υπάρχοντα πακέτα ή ιστορικά δεδομένα.

## v1.0.30
- Προστέθηκε direct frontend είσοδος στο Support Plans: `index.php?option=com_supportplans&view=app`.
- Το login screen είναι ανεξάρτητο οπτικά από Joomla και ζητά username/password.
- Η πιστοποίηση γίνεται από το Joomla `com_users`, όχι από custom password storage.
- Αν ο λογαριασμός έχει Joomla Multi-factor Authentication, το Joomla συνεχίζει αυτόματα στο MFA/OTP challenge πριν ολοκληρωθεί η σύνδεση.
- Μετά την επιτυχή πιστοποίηση ο χρήστης μεταφέρεται στο υπάρχον standalone Support Plans (`tmpl=component`).
- Δεν δημιουργείται δεύτερη βάση χρηστών, password ή OTP secret.


## v1.0.31
- Διορθώθηκε το `404 Invalid controller class: display` στο direct login.
- Προστέθηκε explicit site service provider και Joomla component dispatcher.
- Το site MVC factory πλέον επιλύει σωστά `DisplayController` και `view=app`.


## v1.0.32
- Sidebar χωρίς αριστερό κενό, fixed και full viewport height.
- Σκούρο μπλε sidebar με λευκό active menu item.
- Μεγαλύτερο, πιο εμφανές logo.
- Redesigned fixed header με gradient, shadow και workspace status.
- Responsive tablet/mobile layout.


## v1.0.33
- Διορθώθηκε η πραγματική αιτία του `Invalid controller class: display`.
- Το site service provider χρησιμοποιούσε λάθος namespace (`SupportPlans`) ενώ το manifest και τα PHP classes χρησιμοποιούν `Support`.
- Το frontend MVC factory/dispatcher πλέον χρησιμοποιεί ακριβώς `Support\\Component\\Supportplans`.
- Σημείωση: αν ο server έχει Directory Privacy / HTTP Basic Auth στον `/administrator`, η μετάβαση του standalone app στο `/administrator/...` θα ζητήσει και το server-level credential. Αυτό είναι ανεξάρτητο από το Joomla login/MFA.


## v1.0.34
- Διορθώθηκε η θέση του Joomla component service provider.
- Ο provider βρίσκεται πλέον μόνο στο `administrator/components/com_supportplans/services/provider.php`, όπως απαιτεί το Joomla component bootstrap.
- Αφαιρέθηκε ο λανθασμένος `components/com_supportplans/services/provider.php`.
- Canonical namespace: `Support\\Component\\Supportplans`.


## v1.0.35
- Διορθώθηκε ο πραγματικός λόγος του frontend 404: τα site `DisplayController`, `App HtmlView` και dispatcher είχαν ακόμη namespace `SupportPlans\\...`, ενώ manifest/provider είχαν `Support\\...`.
- Όλα τα frontend classes χρησιμοποιούν πλέον το canonical `Support\\Component\\Supportplans\\Site`.
- Διορθώθηκαν τα παλιά update SQL (`ADD COLUMN IF NOT EXISTS`) που το Joomla Database Maintenance parser διάβαζε λανθασμένα σαν στήλη `IF`.
- Προστέθηκε schema marker 1.0.35 χωρίς destructive αλλαγές.


## v1.0.36
- Full-screen standalone login με `tmpl=component`, χωρίς το header/menu/footer του public site.
- Μετά το login δεν γίνεται redirect στο `/administrator`.
- Ο χρήστης παραμένει στο frontend Support Plans, ώστε το `/administrator` να μπορεί να παραμένει πίσω από Directory Privacy.
- Το login return URL διατηρεί component-only mode.


## v1.0.37
- Υποχρεωτικό δεύτερο βήμα Email OTP μετά το Joomla username/password.
- Τυχαίος 6ψήφιος κωδικός, ισχύς 5 λεπτά, single-use.
- Αποθηκεύεται μόνο password hash του OTP στο Joomla session, όχι ο ίδιος ο κωδικός.
- Μέγιστο 5 αποτυχημένες προσπάθειες.
- Resend cooldown 60 δευτερόλεπτα.
- Αποστολή μέσω του Joomla Mailer στο email του authenticated Joomla user.
- Το frontend SaaS dashboard απαιτεί επιτυχημένο OTP verification.


## v1.0.38
- Το dashboard content είναι πλέον fluid και αξιοποιεί όλο το διαθέσιμο πλάτος δεξιά από το sidebar.
- KPI panels: 3 στήλες desktop, 2 tablet, 1 mobile.
- Cards/panels/tables δεν περιορίζονται πλέον από fixed max-width.
- Τα tables έχουν responsive horizontal overflow όταν χρειάζεται.
- Το εταιρικό logo στο header εμφανίζεται πλέον φυσικά, χωρίς λευκό κουτί, border, radius ή shadow.

## v1.0.39
- Το Media ανοίγει native image picker και το επιλεγμένο logo ανεβαίνει στο `images/supportplans` κατά την αποθήκευση.
- PNG/JPG/WEBP/GIF/SVG validation και αντικατάσταση προηγούμενου uploaded logo.
- Τα help texts για Λογότυπο και Δευτερεύον χρώμα branding έγιναν hover/focus tooltips.
- Προστέθηκε σωστό κενό μεταξύ logo path και Media button.


## v1.0.40
- Logo upload button renamed to Upload logo and aligned with the neighbouring fields.
- Added optional PDF watermark image upload and path setting.
- Watermark renders subtly on every PDF page.
- PDF now ends with Net value, VAT amount and Grand total including VAT.


## v1.0.41
- Το βασικό branding χρώμα παραμένει το background στο PDF.
- Το δεύτερο χρώμα είναι πλέον το foreground/text πάνω στο βασικό χρώμα.
- Αφαιρέθηκαν οι δεύτερες χρωματικές μπάρες/borders.
- Σύνοψη πλάνου, section headings, υπόλοιπο και αριθμός σελίδας χρησιμοποιούν το δεύτερο χρώμα για τα γράμματα.

## v1.0.42
- Πλήρως ανεξάρτητο Support Plans login flow: δεν γίνεται submit στο com_users και δεν καλείται Joomla application login.
- Username/password ελέγχονται απευθείας έναντι του Joomla user password hash με UserHelper::verifyPassword.
- Το Joomla MFA δεν ενεργοποιείται στο standalone Support Plans login.
- Μετά τα σωστά credentials αποστέλλεται υποχρεωτικά 6ψήφιο email OTP.
- OTP: 5 λεπτά, single-use, έως 5 προσπάθειες, resend cooldown 60 sec.
- Ιδιωτικό Support Plans session μετά το OTP, χωρίς Joomla login session.
- Αποσύνδεση/Disconnect καθαρίζει το Support Plans session και επιστρέφει στο standalone login.
- Blocked users και users με requireReset δεν επιτρέπονται.
- 5 αποτυχημένα password attempts επιφέρουν session lock 60 sec.


## v1.0.43
- Οι standalone Login/OTP κάρτες μετακινήθηκαν ψηλότερα στην οθόνη.
- Νέα ρύθμιση Upload login icon. Το icon αντικαθιστά το SP στις Login/OTP οθόνες.
- Τα upload Logo, Watermark και Login icon εμφανίζονται ως full-width σειρές, η μία κάτω από την άλλη.
- Το Login icon αποθηκεύεται στο images/supportplans/ όπως τα υπόλοιπα branding assets.
- Το browser popup «Save password» δεν παράγεται από το component· είναι UI του browser/password manager. Το component διατηρεί σωστά autocomplete attributes.


## v1.0.44
- Προστέθηκε εμφανές κουμπί «Αποθήκευση» στις Ρυθμίσεις.
- Η μπάρα αποθήκευσης είναι sticky στο κάτω μέρος ώστε να είναι εύκολα προσβάσιμη και σε μεγάλη φόρμα.
- Το Login/OTP δεν χρησιμοποιεί πλέον vh για την κατακόρυφη θέση του. Έχει σταθερό responsive top spacing ώστε browser password popups να μην αλλάζουν οπτικά τη θέση της κάρτας.


## v1.0.45
- Διορθώθηκε η αποθήκευση του Login icon: σωστό field name, upload handler και αποθήκευση στα component params.
- Το Upload login icon αποθηκεύει το αρχείο στο images/supportplans/login-icon.ext.
- Το Login χρησιμοποιεί πλέον το configured Login icon, όπως και το OTP.
- Διορθώθηκε invalid CSS padding στο Login που έκανε τον browser να αγνοεί ολόκληρη τη δήλωση.
- Login και OTP είναι πλέον αγκυρωμένα κοντά στην κορυφή με σταθερό top padding και όχι grid/vh positioning.
- Το κουμπί Αποθήκευση καλεί ρητά settings.apply.


## v1.0.46
- Login icon upload μέσω Joomla File::upload.
- Επιβεβαιωμένο multipart/form-data στη φόρμα.
- Έλεγχος writable στο images/supportplans.
- Αναλυτικό μήνυμα σε upload error / permissions / temp path.


## v1.0.47
- Το πεδίο Login icon διαβάζεται πλέον απευθείας από το αποθηκευμένο params JSON του component.
- Οι Ρυθμίσεις δεν βασίζονται σε cached ComponentHelper params για το Login icon.
- Login και OTP διαβάζουν επίσης την τρέχουσα αποθηκευμένη διαδρομή απευθείας από #__extensions.
- Μετά το Save γίνεται verification ότι το login_icon γράφτηκε πραγματικά στη βάση.
- Έτσι το URL/path παραμένει στο πεδίο μετά από Save/reload, όπως στο Logo.


## v1.0.48
- Το Login icon δείχνει μόνιμα κάτω από το πεδίο την αποθηκευμένη διαδρομή με ένδειξη ✓.
- Το Watermark δείχνει επίσης την αποθηκευμένη διαδρομή.
- Νέα ρύθμιση Watermark opacity 0–100% με slider και live ποσοστό.
- Το opacity εφαρμόζεται στο watermark κάθε σελίδας του PDF.
- Default 7%, κοντά στην προηγούμενη σταθερή τιμή 6.5%.
- Διορθώθηκε επίσης η αποθήκευση των υπόλοιπων εμφανών εταιρικών/PDF settings που δεν περνούσαν όλα από τον custom Settings controller.


## v1.0.49
- Προστέθηκε ρύθμιση Standalone Menu Item ID, default 785.
- Το standalone Support Plans διατηρεί Itemid=785 σε Login, OTP, εφαρμογή και εσωτερικά URLs.
- Το Itemid δίνει στα Joomla integrations (π.χ. Web357 Page Visibility) σταθερό menu context.
- Ο launcher ανοίγει το standalone app με Itemid=785.


## v1.0.50
- Διόρθωση standalone Itemid flow.
- Το Login/OTP δεν εμπιστεύεται πλέον Itemid που μπορεί να αντικαταστήσει το Joomla (π.χ. Itemid=1).
- Το configured Standalone Menu Item ID χρησιμοποιείται απευθείας σε authentication, OTP και redirects.
- Default/configured value: 785.


## v1.0.51
- Μετά το OTP ανοίγει πλέον το πλήρες standalone management workspace και όχι το παλιό customer-only view.
- Frontend standalone management για Πακέτα, Πλάνα και Ρυθμίσεις.
- Προστέθηκαν frontend management controllers για package/work/service-plan/settings actions.
- Όλα τα management views/tasks προστατεύονται από το private Support Plans OTP session.
- Διατηρείται το configured Itemid (785) σε όλο το standalone flow.
- Ο Joomla launcher ανοίγει το frontend app/login και δεν απαιτεί /administrator.


## v1.0.52
- Διορθώθηκαν τα links «Άνοιγμα» στα Πακέτα.
- Διορθώθηκαν τα links «Επεξεργασία» στα Πλάνα.
- Τα record IDs (plan_id / id) μπαίνουν πλέον σωστά πριν από το Itemid=785.
- Διορθώθηκαν και τα related form/report/work-entry redirects ώστε να διατηρούν record ID + standalone menu context.


## v1.0.53
- Fix: standalone Ρυθμίσεις no longer calls Joomla Administrator ToolbarHelper.
- Removed administrator-only toolbar calls from frontend Site views.
- Keeps standalone Itemid 785 context.


## v1.0.54
- Fix PDF/report from standalone management.
- Report lookup no longer restricts the package to the Joomla login user's user_id.
- Access remains protected by the Support Plans OTP session/dispatcher.
- Prevents the null-to-object typed property error shown when printing a customer package.
- Report URL preserves standalone context and Itemid 785.


## v1.0.55
- Fix PDF error: Call to a member function get() on null.
- Report Site view now loads com_supportplans component params explicitly.
- Report template includes a defensive ComponentHelper fallback.
- Branding/report settings can now be read correctly in standalone PDF output.


## v1.0.56
- Restored the financial VAT summary at the bottom of the PDF report.
- Shows Net value, VAT amount/rate, and Total incl. VAT.
- Uses the package snapshot initial_amount and vat_rate.
- Print-friendly block avoids splitting across pages.


## v1.0.57
- Corrected VAT summary placement: it is now inside printable PDF page 2.
- v1.0.56 had placed the block after the closing report container, so print CSS could omit it.
- Summary shows Net, VAT, Total incl. VAT and is explicitly visible in print.
- For ongoing plans Net uses accumulated work cost; prepaid/subscription use package value.


## v1.0.58
- Removed the redundant orange summary bar below the work table.
- Added total billable time as the first row in Financial Summary.
- Billable time format is HH:MM (no seconds).


## v1.0.59
- Fixed standalone Service Plan «Επεξεργασία» link.
- Edit now uses an explicit component URL with mode=edit + id, avoiding Joomla SEF/menu routing collapsing it back to the list.
- New/Close service-plan links use the same reliable standalone route.
- Itemid 785 remains preserved.


## v1.0.60
- Το label «Υποστηριζόμενα websites» στα Πακέτα είναι πλέον παραμετρικό.
- Νέα ρύθμιση: «Ονομασία πεδίου Websites στα Πακέτα».
- Αλλάζει μόνο η ονομασία/label, όχι το database field ούτε τα υπάρχοντα δεδομένα.
- Το Standalone Menu Item ID παραμένει ρύθμιση του component. Στο τρέχον site είναι 785· σε άλλο Joomla site βάζουμε το ID του αντίστοιχου hidden menu item.


## v1.0.61
- Renamed the Settings caption to «Ονομασία πεδίου Υπηρεσιών στα Πακέτα».
- The configured label is now used above the field in package create/edit.
- The same configured label is now used in the package list column and in PDF reports.
- Existing supported_websites data/database field remain unchanged.


## v1.0.64
- Rebuilt PDF summary from clean v1.0.61 so Subscription / Ongoing / Prepaid remain separate.
- Ongoing keeps its own fields: Πραγματικός χρόνος / Χρεώσιμος χρόνος / Κόστος προ Φ.Π.Α.
- Prepaid keeps: Υπόλοιπο χρόνου / Διαθέσιμο ποσό / Χρησιμοποιήθηκαν.
- Subscription keeps its subscription-specific fields.
- Icons are black, without circular backgrounds, and differ by billing mode.


## v1.0.65
- Ongoing PDF summary now has exactly two cards: Συνολικός χρόνος and Συνολικό κόστος.
- Uses black clock and wallet icons without circular backgrounds.
- Prepaid and Subscription summaries are unchanged.


## v1.0.66
- Restyled the Οικονομική σύνοψη on PDF page 2 so it is clearly separated from the work log.
- Added branded border, light tinted background, branded title strip and stronger final-total row.
- Kept the panel compact and right-aligned, with all financial text black.
- Print colors are explicitly preserved.


## v1.0.67
- Page 2 PDF: Financial Summary moved to the bottom and now spans the full available width.
- Added a second full-width panel directly below: «Τράπεζες / Λογαριασμοί».
- No icons were added; styling follows the existing PDF.
- Settings now include up to 3 bank accounts, each with: Τράπεζα, IBAN, Όνομα δικαιούχου.
- Empty bank rows are omitted from the PDF.


## v1.0.68
- PDF last page: «Οικονομική σύνοψη» and «Τράπεζες / Λογαριασμοί» are pinned to the bottom of the last page.
- Both panels remain full width and sit immediately above the existing PDF footer/page number.
- Bank settings and all v1.0.67 functionality are preserved.


## v1.0.69
- Added Διαγραφή action to service plans.
- A plan cannot be deleted while customer packages still reference it, protecting existing package history.
- Removed Grey/Gray/Green service-plan seed rows from installer/update SQL so they are not recreated in future installs/updates.
- Existing renamed plans are not overwritten or recreated.


## v1.0.70
- Bank settings are now one row per account with 3 fields: Τράπεζα / IBAN / Όνομα δικαιούχου.
- Added «+ Προσθήκη τράπεζας» for unlimited additional bank rows.
- Each row can be removed with ×.
- Accounts are stored dynamically as JSON in component settings.
- Existing bank_1..bank_3 settings are automatically used as fallback, so current data is preserved.
- PDF supports all dynamically added bank accounts.


## v1.0.71
- Fresh installations create NO predefined service plans.
- Removed all installer/update SQL seed INSERTs for #__supportplans_serviceplans.
- The service-plan table is created empty; users create only the plans they need.
- Updates do not delete or rename plans already created by the user.


## v1.0.72
- Fixed the actual cause of deleted plans returning: script.php was recreating Grey, Green, Ongoing and Subscription during every component install/update.
- Removed that installer/postflight seed logic completely.
- Updates no longer create, restore, rename or delete service plans.
- Fresh installations also start with an empty service-plan list.
- Added spacing below «+ Νέο πλάνο» so it no longer touches the panel below.


## v1.0.73
- Entire printable PDF/report now uses the Geom Google Font (weights 300-900), including headings, tables, summaries, bank accounts and footer.
- Added spacing below the package edit top action buttons (Αποθήκευση / Αποθήκευση & κλείσιμο / Κλείσιμο) so they no longer touch the panel below.


## v1.0.74
- Detailed work report: work entries are easier to distinguish with subtle alternating row backgrounds, clearer separators and slightly more vertical padding.
- Added 18px extra separation between «Οικονομική σύνοψη» and «Τράπεζες / Λογαριασμοί».
- Existing PDF branding and Geom font are preserved.


## v1.0.75
- Fixed alternating work-entry backgrounds in print/PDF using stronger selectors and exact print-color adjustment.
- Odd work rows now use a clearly visible light gray; even rows remain white.
- Moved the combined bottom block (Οικονομική σύνοψη + Τράπεζες / Λογαριασμοί) exactly 50px lower on the last PDF page.


## v1.0.76
- In «Αναλυτικό δελτίο εργασιών», the «Διάρκεια / κόστος» heading and all duration/cost values are now right-aligned.
- Existing alternating work-row backgrounds, Geom font and bottom summary positioning are preserved.


## v1.0.77
- Increased typography in «Οικονομική σύνοψη» so the financial information has stronger visual priority.
- Title is larger/bold, financial rows are slightly larger, and the final total is emphasized further.
- Bank panel and the rest of the PDF keep their current sizing.


## v1.0.78
- Financial summary typography is now visibly larger than the rest of the PDF.
- Summary title: 16px / 800.
- Financial rows: 14px.
- Final total: 15px / 800.
- Added slightly more row height so the larger typography reads clearly.


## v1.0.79
- «Οικονομική σύνοψη» title: 19px bold.
- Financial detail rows: 16px.
- «Σύνολο με Φ.Π.Α.»: 17px bold.


## v1.0.80
- Corrected financial-summary font sizing against the actual div-based report markup.
- Title 19px bold, rows 16px, final total 17px bold.
- Added print-specific overrides so the browser print preview cannot fall back to the smaller inherited PDF text size.


## v1.0.81
- Complete visual removal of Web357 CPNB from every standalone Support Plans page.
- Includes Login, OTP, Packages, Plans, Settings, edit pages and Report/PDF.
- Removes the bottom-left cookie-control button from PDF/print.
- Web357 remains active on the normal public website.
- Bundled helper system plugin is installed and enabled automatically.


## v1.0.82
- Added in-app standalone «Οδηγίες» page.
- Added in-app Changelog tab.
- Documentation covers setup, OTP login, service plans, customer packages, work entries, PDF and banks.
- No v2 migration/change; this remains the v1.x update line.

## v1.0.83
- Help is now inside the exact app shell with sidebar/header.
- Help routing now includes tmpl=component.
- UI term standalone replaced by Service Hub, suitable for future quotations.

## v1.0.84
- Renamed Κέντρο Υπηρεσιών to Service Hub.
- Fixed Settings sidebar icon on Help page by reusing the exact known-good sidebar SVG markup.

## v1.0.85
- Fixed Help sidebar disappearing items by copying the complete known-good sidebar from Packages.
- Packages, Plans, Settings and Help now remain visible together; only Help receives active state on the Help page.

## v1.1.0
- Νέα ενότητα Προσφορές (Service Hub → Προσφορές) με τρεις καρτέλες: Προσφορές, Templates, Υπηρεσίες.
- Υπηρεσίες: βιβλιοθήκη με τίτλο ενότητας, κείμενο περιγραφής (απλή μορφοποίηση: λίστες, έντονα, παραπομπές, μεταβλητές), περιγραφή και τιμή για τον πίνακα τιμών.
- Templates: τίτλος εξωφύλλου, υπότιτλος, footer, όροι πληρωμής, όροι/παραπομπές, Φ.Π.Α., τράπεζες και προεπιλεγμένη επιλογή υπηρεσιών (περιγραφή / πίνακας τιμών / σειρά).
- Προσφορές: δημιουργούνται ως snapshot του template· ανά υπηρεσία επιλέγεται αν περιγράφεται και αν μπαίνει στον πίνακα τιμών, με τιμή, έκπτωση και σειρά. Καταστάσεις: Πρόχειρη, Απεσταλμένη, Αποδεκτή, Απορρίφθηκε. Αντίγραφο ως νέα προσφορά.
- Pdf προσφοράς μέσω browser print: εξώφυλλο, αριθμημένες ενότητες, πίνακας Τιμή/Έκπτωση/Αξία/ΦΠΑ/Σύνολο, κείμενο συνόλων, τράπεζες, όροι, υπογραφή, αυτόματη σελιδοποίηση A4 με footer και αριθμό σελίδας.
- Προαιρετικό έτοιμο παράδειγμα template (e-shop, φιλοξενία, συντήρηση).
- Νέοι πίνακες: `#__supportplans_offer_services`, `#__supportplans_offer_templates`, `#__supportplans_offers`, `#__supportplans_offer_items`.

## v1.1.1
- Joomla 6: το script εγκατάστασης δεν χρησιμοποιεί πλέον τις κλάσεις `Joomla\CMS\Filesystem` (υπάρχουν μόνο με το plugin συμβατότητας) ούτε τη στήλη `system_data`, που αφαιρέθηκε. Έτσι εγκαθίστανται ξανά τα plugins «Support Plans Launcher» και «No CPNB».
- Ο launcher ανοίγει το Service Hub σε νέο tab και από το «Components → Support Plans», με το Itemid των Ρυθμίσεων.
- Οι Ρυθμίσεις (site/admin) χρησιμοποιούν `Joomla\Filesystem\File/Folder` για τα uploads.
- Σφάλματα εγκατάστασης plugin εμφανίζονται πλέον ως μήνυμα αντί να αγνοούνται.

## v1.1.2
- Οπτικός επεξεργαστής (WYSIWYG) για τα κείμενα των Προσφορών: κείμενο υπηρεσίας, κείμενο της προσφοράς ανά υπηρεσία, όροι πληρωμής και όροι/παραπομπές σε templates και προσφορές.
- Εργαλειοθήκη: στυλ (κείμενο / υπότιτλος με υπογράμμιση / έντονος υπότιτλος), έντονα, πλάγια, υπογράμμιση, κουκκίδες, αρίθμηση, παραπομπή με αυτόματο επόμενο αριθμό (Ctrl+.), σύνδεσμος, μεταβλητές, καθαρισμός, αναίρεση/επανάληψη, προβολή κώδικα.
- Ο επεξεργαστής αποθηκεύει στην υπάρχουσα απλή μορφοποίηση, οπότε τα αποθηκευμένα κείμενα και το Pdf δεν αλλάζουν. Προστέθηκαν πλάγια `*…*` και υπογράμμιση `__…__`.
- Επικόλληση ως καθαρό κείμενο· γραμμές με κουκκίδες (•, ▪, –) γίνονται λίστα.
- Νέο αρχείο `media/js/offers-editor.js`.

## v1.2.0
- OTP: 6 κουτάκια (ένα ανά ψηφίο) με αυτόματη μετάβαση, Backspace/βέλη, επικόλληση και αυτόματη συμπλήρωση (one-time-code), αυτόματη υποβολή.
- OTP: διάρκεια 2 λεπτά (ήταν 5) με μπάρα αντίστροφης μέτρησης (πράσινο → πορτοκαλί → κόκκινο). Ο χρόνος υπολογίζεται από τη λήξη που έχει αποθηκευτεί στο session, οπότε μένει σωστός και μετά από ανανέωση της σελίδας. Το «Αποστολή νέου κωδικού» μετρά αντίστροφα τα 60″.
- Προσφορές: δικές σας μεταβλητές σε templates (με προεπιλεγμένη τιμή) και προσφορές. Νέα στήλη `variables` στους πίνακες `#__supportplans_offer_templates` και `#__supportplans_offers`, που προστίθεται από το script.php.
- Οι μεταβλητές εφαρμόζονται σε κείμενα, τίτλους ενοτήτων, περιγραφές του πίνακα τιμών, υπότιτλο και τίτλους τιμών/όρων. Επιτρέπονται ελληνικά ονόματα.
- Προσφορές: Προεπισκόπηση μέσα σε παράθυρο με τα τρέχοντα (μη αποθηκευμένα) δεδομένα της φόρμας (`task=offer.preview`, χωρίς εγγραφή στη βάση), με ανανέωση, εκτύπωση και αποθήκευση.

## v1.2.1
- Πακέτα: επεξεργασία καταχωρημένης εργασίας. Το εικονίδιο μολυβιού φορτώνει την εργασία στη φόρμα και την ενημερώνει με επανυπολογισμό χρεώσιμου χρόνου και κόστους.
- Πακέτα: δύο μικρά εικονίδια (επεξεργασία / διαγραφή) αντί για το κουμπί «Διαγραφή», και μικρότερο κείμενο στη στήλη «Αίτημα».
- Η διαγραφή και η ενημέρωση εργασίας περιορίζονται στο πακέτο στο οποίο ανήκει η εργασία.
- Pdf αναφοράς: στα προπληρωμένα πλάνα δεν εμφανίζονται η Οικονομική σύνοψη και οι Τράπεζες / Λογαριασμοί.

## v1.3.0
- Προσφορές → καρτέλα **Μεταβλητές**: κοινοί ορισμοί πεδίων (ετικέτα, μεταβλητή, τύπος μίας/πολλών γραμμών, προεπιλεγμένη τιμή, σειρά), αποθηκευμένοι στις παραμέτρους του component (`offer_fields`).
- Κάθε προσφορά (και η «Νέα προσφορά») εμφανίζει ένα πεδίο ανά μεταβλητή. Οι τιμές αποθηκεύονται στη στήλη `variables` της προσφοράς και αντικαθιστούν τα `{κλειδιά}` σε κείμενα, τίτλους και περιγραφές του πίνακα τιμών.
- Εντοπισμός μεταβλητών που χρησιμοποιούνται σε Υπηρεσίες/Templates αλλά δεν έχουν οριστεί, με προσθήκη σε ένα κλικ.
- Συνώνυμα `{client}` → `{customer}` και `{project_name}` → `{project}`.
- Αφαιρέθηκε η κάρτα μεταβλητών ανά template και ανά προσφορά (v1.2.0). Τιμές που έχουν ήδη αποθηκευτεί σε προσφορές εμφανίζονται και διατηρούνται.

## v1.3.1
- Νέο `media/js/sp-sortable.js`: drag & drop με pointer events (ποντίκι, αφή, πένα), λαβή `[data-sort-handle]`, βελάκια πληκτρολογίου και αυτόματο κύλισμα κοντά στις άκρες της οθόνης. Εφαρμόζεται στις Μεταβλητές, στις υπηρεσίες της προσφοράς και στις υπηρεσίες του template (εκεί αντικαθιστά τα πεδία «Σειρά»).
- Εικονίδια: λαβή ⋮⋮, κάδος για αφαίρεση, μικρό τετράγωνο «+» για νέα μεταβλητή.
- Συμπαγές σύστημα κουμπιών σε όλο το Service Hub: 34px ύψος, 13.5px/600 (μικρά: 27px, 12.5px), tabs 13px.
- Placeholders «Επωνυμία» / «Project» στη Νέα προσφορά, αντί για στοιχεία πραγματικού πελάτη.

## v1.3.2
- Μεταβλητές: οι οδηγίες κάτω από τη λίστα αντικαταστάθηκαν από εικονίδιο ⓘ δίπλα στον τίτλο, με popover (hover/focus στον υπολογιστή, πάτημα σε αφή, Esc ή κλικ έξω για κλείσιμο). Επαναχρησιμοποιήσιμο μοτίβο `.sp-info`.

## v1.4.0
- Διάταξη ανά υπηρεσία στο Pdf: «Συνέχεια στην ίδια σελίδα» με απόσταση σε mm (0–80) από την προηγούμενη ενότητα, ή «Ξεκινά σε νέα σελίδα». Προεπιλογές στη βιβλιοθήκη Υπηρεσιών, αντιγράφονται στη νέα προσφορά. Νέες στήλες `page_break` και `space_before` στους πίνακες `#__supportplans_offer_services` και `#__supportplans_offer_items` (από το script.php).
- Μια ενότητα στην αρχή σελίδας δεν σπρώχνεται προς τα κάτω από την απόσταση.
- Λίστες σε 2 στήλες: κουμπί «Στήλες» στον επεξεργαστή και σήμανση `[[2 στήλες]]` / `[[1 στήλη]]` στη γραμμή πριν από τη λίστα. Ο κανόνας «αριθμημένες λίστες από 10 στοιχεία σε 2 στήλες» ισχύει όταν δεν υπάρχει σήμανση.
- Οι λίστες 2 στηλών χωρίζονται πλέον ανάμεσα σε σελίδες όπως και οι απλές.

## v1.5.0
- Header στο Pdf προσφοράς σε όλες τις σελίδες εκτός από το εξώφυλλο: λογότυπο (ή επωνυμία αν δεν υπάρχει λογότυπο), κείμενο δεξιά, λεπτή γραμμή από κάτω. Το περιεχόμενο κάθε σελίδας ξεκινά 8mm χαμηλότερα όταν υπάρχει header.
- Ρυθμίσεις ανά template και ανά προσφορά: `show_header` (ενεργό από προεπιλογή, και για τις υπάρχουσες προσφορές) και `header_text` (δέχεται μεταβλητές). Κενό κείμενο → «υπότιτλος + αριθμός · έργο». Οι στήλες προστίθενται από το script.php.

## v1.5.1
- Pdf προσφοράς: ο πίνακας τιμών έγινε κάρτα «Οικονομική σύνοψη», όπως στις αναφορές: πλαίσιο στο χρώμα branding, μπάρα τίτλου, στήλες Περιγραφή υπηρεσίας / Αξία / Φ.Π.Α. / Σύνολο, τονισμένη γραμμή «Σύνολο». Η στήλη Έκπτωση αφαιρέθηκε· η Αξία είναι μετά την έκπτωση και η συνολική έκπτωση αναφέρεται στο κείμενο κάτω από τον πίνακα.
- Οι τράπεζες εμφανίζονται ως κάρτα «Τράπεζες / Λογαριασμοί» (Τράπεζα / IBAN / Όνομα δικαιούχου).
- Οι κάρτες είναι πίνακες με thead, οπότε αν σπάσουν σε δύο σελίδες ο τίτλος και οι επικεφαλίδες επαναλαμβάνονται.
- Geom σε όλο το έγγραφο με `!important`, ώστε να μην το αλλάζει το template του site.
- Εξώφυλλο: οι γραμμές Αρ. Προσφοράς / Ημερομηνία / Πελάτης καλύπτουν όλο το πλάτος περιεχομένου.

## v1.5.2
- Διάταξη Pdf και για τις ενότητες «Αξία Έργου – Τρόπος Πληρωμής» και «Όροι Προσφοράς»: νέα σελίδα ή συνέχεια με απόσταση σε mm. Ρυθμίζεται στο template (αντιγράφεται στη νέα προσφορά) και σε κάθε προσφορά. Νέες στήλες `pricing_break`, `pricing_space`, `terms_break`, `terms_space` (από το script.php).
- Κοινή συνάρτηση `OfferHelper::sectionLayout()` για όλες τις επικεφαλίδες ενοτήτων.

## v1.5.3
- Τίτλος «Οικονομική σύνοψη»: χρώμα από τη ρύθμιση «Χρώμα κειμένου πάνω στο branding», όπως ο τίτλος των Τραπεζών, στην οθόνη και στην εκτύπωση. Ισχύει για τα Pdf προσφορών και πακέτων (πριν ένας κανόνας print τον έκανε πάντα μαύρο).
- Pdf πακέτων: το αναλυτικό δελτίο εργασιών είναι κάρτα με μπάρα τίτλου, γκρι γραμμή επικεφαλίδων και διαχωριστικά, στο στυλ της κάρτας Τράπεζες / Λογαριασμοί. Το αίτημα εμφανίζεται έντονο και η περιγραφή μικρότερη από κάτω.

## v1.5.4
- Header Pdf προσφοράς: «Μέγεθος λογοτύπου header (px)» (12–120, προεπιλογή 28) και «Απόσταση περιεχομένου από το header (mm)» (0–40, προεπιλογή 6). Ρυθμίζονται στο template (αντιγράφονται στη νέα προσφορά) και σε κάθε προσφορά. Νέες στήλες `header_logo`, `header_gap` (από το script.php).
- Το περιεχόμενο κάθε σελίδας ξεκινά στο «header + απόσταση» και τελειώνει πάντα στο ίδιο σημείο πάνω από το footer (271mm). Το ύψος της περιοχής περιεχομένου υπολογίζεται από τις δύο ρυθμίσεις, οπότε η σελιδοποίηση προσαρμόζεται μόνη της.

## v1.6.0
- Δομημένες λίστες σε υπηρεσίες: κουμπί «＋ Λίστα» στη βιβλιοθήκη Υπηρεσιών και σε κάθε υπηρεσία της προσφοράς. Κάθε λίστα έχει τίτλο (προαιρετικό), επιλογή 1/2 στηλών και στοιχεία ένα ανά γραμμή (αφαιρούνται αυτόματα κουκκίδες/αρίθμηση από επικόλληση). Μια υπηρεσία μπορεί να έχει πολλές λίστες.
- Pdf: κάθε λίστα αποδίδεται μετά το κείμενο της υπηρεσίας ως κάρτα στο στυλ «Τράπεζες / Λογαριασμοί» (μπάρα τίτλου, διαχωριστικά). Στις 2 στήλες τα στοιχεία μοιράζονται κατά στήλη. Υποστηρίζονται **έντονα**, ^παραπομπές και μεταβλητές.
- Νέα στήλη `lists` (JSON) στους πίνακες `#__supportplans_offer_services` και `#__supportplans_offer_items` (από το script.php). Οι λίστες της βιβλιοθήκης αντιγράφονται στη νέα προσφορά και όταν προστίθεται υπηρεσία από τη βιβλιοθήκη. Περιλαμβάνονται στην προεπισκόπηση και στο αντίγραφο προσφοράς.
- Νέο αρχείο `media/js/offer-lists.js`.

## v1.6.1
- «＋ Κείμενο» δίπλα στο «＋ Λίστα»: η στήλη `lists` κρατά πλέον μπλοκ με τύπο (`list` ή `text`), με τη σειρά που ορίζει ο χρήστης (drag & drop). Μπλοκ χωρίς τύπο (v1.6.0) διαβάζονται ως λίστες.
- Λίστες: νέο πεδίο «Περιγραφή λίστας» (`desc`), που αποδίδεται μέσα στην κάρτα κάτω από τη μπάρα τίτλου.
- Επεξεργαστής σε «inline» λειτουργία (`data-sp-editor="inline"`) για στοιχεία και περιγραφή λίστας: έντονα, πλάγια, υπογράμμιση, παραπομπή, σύνδεσμος, μεταβλητές, καθαρισμός, αναίρεση, κώδικας, χωρίς υπότιτλους, λίστες και στήλες. Κάθε γραμμή παραμένει ένα στοιχείο.
- Νέα `OfferHelper::renderBlocks()` / `textBlock()`.

## v1.6.2
- Pdf: οι κάρτες λιστών είναι striped (κάθε δεύτερη γραμμή #f6f7f9, με print-color-adjust).
- Pdf: κενό 5mm πριν και 6mm μετά από κάθε κάρτα λίστας, και 5mm πριν από κάθε επιπλέον μπλοκ κειμένου (κλάση `sp-o-blockstart` στο πρώτο στοιχείο του). Στην αρχή σελίδας το κενό μηδενίζεται.

## v1.6.3
- Header Pdf: προεπιλεγμένο κείμενο «Οικονομική προσφορά {number} · {project}», αντί για τον υπότιτλο του εξωφύλλου που συνήθως είναι με κεφαλαία. Χωρίς text-transform από το template του site.
- Εξώφυλλο: ο τίτλος της προσφοράς σε bold (700).

## v1.6.4
- Διόρθωση αναβάθμισης από παλιές εκδόσεις: το script.php πρόσθετε στήλες με `AFTER` σε στήλες που μπορεί να έλειπαν ακόμα (π.χ. `subscription_months AFTER billing_mode`), με αποτέλεσμα «Unknown column 'billing_mode'». Τώρα ένας ενιαίος κατάλογος στηλών ανά πίνακα (πακέτα, πλάνα, εργασίες, προσφορές) προσθέτει κάθε στήλη που λείπει χωρίς `AFTER`.
- Μετά την αναβάθμιση συμπληρώνονται τιμές στις παλιές εγγραφές: `sequence_no` = id όπου είναι 0, `service_plan_id` από το πλάνο με το ίδιο όνομα, `hourly_rate` των προπληρωμένων πλάνων. Το `request_date` των εργασιών γίνεται varchar(100) όπου ήταν ακόμα DATE.
- Ελέγχθηκε με αναβάθμιση από πίνακες της v1.0.x με δεδομένα.


## v1.6.5
- Η postflight αντιγράφει ξανά τον φάκελο `media` στο `/media/com_supportplans` με απλή PHP αντιγραφή και δικαιώματα 0644/0755. Μια αποτυχημένη αναβάθμιση (π.χ. το σφάλμα `billing_mode`) σβήνει με rollback τα αρχεία media που είχε δημιουργήσει, και κάποιοι hosts αντιγράφουν αρχεία χωρίς δικαίωμα ανάγνωσης. Και στις δύο περιπτώσεις το Service Hub έβγαινε χωρίς CSS.

## v1.6.6
- Νέος `Site\Helper\Assets`: τα `supportplans.css`, `offers.css` και τα scripts των προσφορών τυπώνονται inline (`<style>`/`<script>`) μέσα στο output του component, αντί για `<link>` στο `<head>`. Σε sites όπου το `component.php` του template δεν τυπώνει τα styles του head, ή όπου κάποιο plugin/server μπλοκάρει το `/media`, το Service Hub έβγαινε χωρίς μορφοποίηση. Αν ένα αρχείο δεν διαβάζεται, γίνεται fallback στο HTMLHelper.

## v1.7.0
- Εισαγωγή προσφοράς: νέες στήλες `intro_title` / `intro_body` στα `offer_templates` και `offers`, που προστίθενται με ασφάλεια από το script.php. Στη φόρμα template/προσφοράς μπαίνουν πριν από τα κείμενα πληρωμής/όρων. Η νέα προσφορά τις αντιγράφει από το template. Στο Pdf αποδίδονται ως πρώτη αριθμημένη ενότητα της ροής, με προεπιλεγμένο τίτλο «Εισαγωγή», και μόνο όταν το κείμενο δεν είναι κενό. Υποστηρίζουν μεταβλητές.

## v1.7.1
- Λίστες/κείμενα στην Εισαγωγή: νέα στήλη `intro_lists` (ίδιο JSON με το `lists` των υπηρεσιών) στα `offer_templates` και `offers`. Η φόρμα χρησιμοποιεί το `OfferHelper::listsEditor('intro_lists', …)`, το Pdf το `renderBlocks` μετά το κείμενο εισαγωγής. Η ενότητα εμφανίζεται αν υπάρχει κείμενο ή έστω ένα block.
