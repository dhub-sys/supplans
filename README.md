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

## v1.7.2
- Branding: με λογότυπο κρύβεται το κείμενο δίπλα του. Στη σύνδεση/OTP δεν τυπώνεται το «Support Plans / Workspace». Στην appbar το `.sp-brand:has(.sp-brandlogo) .sp-brand-copy` κρύβεται.
- Νέα ρύθμιση `page_title` (site/admin settings, config.xml). Ο Dispatcher την ορίζει ως τίτλο του document και τη χρησιμοποιούν και οι σελίδες σύνδεσης/OTP. Το offerdoc κρατά τον δικό του τίτλο, που γίνεται όνομα αρχείου Pdf.
- Launcher του admin μενού: ίδια markup με τα native στοιχεία του Atum (`li.item.item-level-1 > a.no-dropdown > span.icon-fw + span.sidebar-item-title`). Κρύβεται το εικονίδιο που βάζει το Atum σε κάθε `a[target=_blank]`.

## v1.7.3
- `Site\Helper\AuthCookie`: υπογεγραμμένο cookie `sp_hub_auth` (HttpOnly, SameSite=Lax, Secure σε https) με `userId.expires.HMAC-SHA256(secret, userId|expires|password hash)`. Εκδίδεται στο `otp.verify`, σβήνει στο `auth.logout`. Αλλαγή κωδικού, μπλοκάρισμα ή μικρότερη διάρκεια στις ρυθμίσεις το ακυρώνουν.
- Dispatcher: αν η συνεδρία δεν είναι συνδεδεμένη, επαναφέρει τη σύνδεση από το cookie. Οι οθόνες σύνδεσης/OTP χωρίς task ανακατευθύνουν στα Πακέτα όταν ο χρήστης είναι ήδη συνδεδεμένος.
- Νέα ρύθμιση `login_lifetime` (ώρες, προεπιλογή 12, 0 = χωρίς cookie, μέγιστο 2160).

## v1.8.0
- Υπηρεσίες / Περιγραφές: νέα στήλη `kind` (`service` | `description`) στα `offer_services` και `offer_items`. Όταν προστίθεται η στήλη, οι εγγραφές με `in_table=0` και `price=0` γίνονται `description`. Οι περιγραφές αποθηκεύονται πάντα με τιμή/έκπτωση 0 και `in_table=0`, και οι φόρμες κρύβουν τα πεδία τιμής (`.sp-svc-only`). Υπάρχουν badges στις λίστες και optgroups στη βιβλιοθήκη της προσφοράς.
- Επεξεργασία προσφοράς: κάθε γραμμή είναι accordion. Η μπάρα δείχνει λαβή, τύπο, τίτλο, ετικέτες κατάστασης, αξία, chevron και διαγραφή, και ενημερώνεται ζωντανά. Οι αποθηκευμένες γραμμές ανοίγουν κλειστές, οι νέες ανοιχτές.

## v1.9.0
- Νέος `kind` = `pricing` («Τιμές»). Στο Pdf αποδίδει ενότητα με τον πίνακα «Οικονομική σύνοψη», τη φράση τελικής τιμής, το κείμενο και τις λίστες του block και τους τραπεζικούς λογαριασμούς (`show_banks` της προσφοράς). Δεν εμφανίζεται αν δεν υπάρχουν γραμμές τιμών, κείμενο ή λίστες.
- Πίνακας με έκπτωση: όταν `totals.discount > 0` ο πίνακας έχει 6 στήλες (Τιμή, Έκπτωση, Αξία, Φ.Π.Α., Σύνολο) και την κλάση `has-discount`. Αλλιώς έχει 4.
- Οι φόρμες template/προσφοράς δεν έχουν πια τίτλο τιμών, κείμενο πληρωμής, όρους ή layout αυτών των ενοτήτων. Τα controllers δεν τα αποθηκεύουν.
- Μεταφορά δεδομένων (`script.php::migrateOfferBlocks`, μία φορά, σημαία `offer_blocks_v190` στις παραμέτρους):
  - Templates: δημιουργούνται εγγραφές βιβλιοθήκης «Τιμές – {template}» και «Όροι – {template}», με κοινή χρήση όταν είναι ίδιες, και μπαίνουν στο τέλος των items.
  - Προσφορές: προστίθενται γραμμές pricing και description στο τέλος, με τον ίδιο τίτλο, κείμενο και layout. Τα παλιά πεδία αδειάζουν.
  - Η μεταφορά γίνεται σε transaction.
  - Το offerdoc κρατά fallback για τυχόν μη μεταφερμένα δεδομένα.
- Το παράδειγμα (OfferSamples) περιλαμβάνει block τιμών και όρους ως εγγραφές της βιβλιοθήκης.

## v1.10.0
- Σύνδεσμος πελάτη: νέα στήλη `plans.share_token` με 96 hex χαρακτήρες από `random_bytes(48)`.
  - URL: `index.php?option=com_supportplans&view=report&tmpl=component&t=<token>`.
  - Ο Dispatcher επιτρέπει το `view=report` χωρίς σύνδεση μόνο με έγκυρη μορφή token. Το `Report\HtmlView` φορτώνει το πακέτο από το token (`hash_equals`), αλλιώς επιστρέφει 404.
  - Headers `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet`, `Referrer-Policy: no-referrer`, `Cache-Control: private, no-store`, και meta robots/referrer.
- `ShareController`: `share.create` (νέο ή αντικατάσταση), `share.revoke` και `share.send` (email με το mailer του Joomla, HTML και text). Όλα απαιτούν σύνδεση και CSRF token.
- Νέα κάρτα «Σύνδεσμος για τον πελάτη» στην οθόνη του πακέτου. Το email προσυμπληρώνεται από τον χρήστη Joomla του πακέτου, αν υπάρχει.

## v1.10.1
- Ο σύνδεσμος πελάτη περιέχει `spapp=1&Itemid={standalone_itemid}`, όπως το iframe εκτύπωσης του Service Hub: ίδιο template style του site και χωρίς Web357 cookie banner. Οι παλιοί σύνδεσμοι κάνουν 301 στο πλήρες URL.

## v1.10.2
- Σύνδεσμος πελάτη: η αναφορά εφαρμόζει στην οθόνη τους ίδιους κανόνες `@media print` με την εκτύπωση. Ένα script τους αντιγράφει από τα stylesheets της σελίδας σε `<style>`, και με μερικά screen overrides οι σελίδες φαίνονται ως φύλλα A4 κεντραρισμένα σε γκρι φόντο. Σε στενές οθόνες γίνεται κλιμάκωση με `zoom`, που μηδενίζεται στο `beforeprint`. Το Pdf του συνδέσμου είναι ίδιο pixel προς pixel με την εκτύπωση από το Service Hub.

## v1.10.3
- Αναφορά πακέτου: η 2η σελίδα έχει σταθερό ύψος A4 με `overflow:hidden`, οπότε οι εργασίες που δεν χωρούσαν κόβονταν. Ένας client-side paginator (μετά το `document.fonts.ready`, `window.spReportReady`) μοιράζει τις γραμμές του πίνακα εργασιών σε σελίδες συνέχειας (`.sp-report-page-more`: ίδια κάρτα και thead, τίτλος «(συνέχεια)») και βάζει την οικονομική σύνοψη στην τελευταία σελίδα ή σε δική της, αν δεν χωρά. Μετά επαναριθμεί τις σελίδες.
- Οι κανόνες `@media print` εφαρμόζονται πλέον στην οθόνη και στην εκτύπωση από το Service Hub, όχι μόνο στον σύνδεσμο πελάτη, ώστε οι μετρήσεις της σελιδοποίησης να γίνονται με τη γεωμετρία της εκτύπωσης.

## v1.10.4
- Σελιδοποίηση αναφοράς, πιο ανθεκτική:
  - Η γεωμετρία A4 ορίζεται ως απλό CSS (`.sp-report.sp-paged …`) και δεν εξαρτάται από την αντιγραφή των κανόνων `@media print`. Αν ένας optimizer κάνει τα stylesheets μη αναγνώσιμα (`cssRules` → SecurityError), οι μετρήσεις παραμένουν σωστές.
  - Χωρίς paginator (μπλοκαρισμένο ή αποτυχημένο script) η 2η σελίδα στην εκτύπωση μεγαλώνει (`height:auto`), οπότε ο πίνακας δεν κόβεται.
  - Το script δεν χρησιμοποιεί `document.currentScript`, ξεκινά στο DOMContentLoaded, περιμένει τη γραμματοσειρά έως 1,5 s, έχει περιθώριο ασφαλείας 2 mm και κάνει fallback σε περίπτωση σφάλματος.
- Ελέγχθηκε με 37 εργασίες σε τρία σενάρια: κανονικά, με μπλοκαρισμένα cssRules και χωρίς JavaScript. Σε όλα εμφανίζονται όλες οι εργασίες.

## v1.10.5
- `Helper\EntryOrder`: χρονολογική ταξινόμηση εργασιών με ανάλυση του ελεύθερου `request_date` (d/m/Y, Y-m-d, m/Y, ελληνικοί μήνες + έτος). Όπου δεν αναγνωρίζεται, χρησιμοποιείται το `created`. Εφαρμόζεται στην αναφορά (πριν `ORDER BY request_date` ως κείμενο) και στη λίστα του πακέτου.
- Τίτλος εγγράφου αναφοράς (= όνομα αρχείου Pdf στον browser): `Report\HtmlView::fileTitle()` → «{πελάτης} - {αρ. εντύπου}», χωρίς χαρακτήρες που απαγορεύονται σε ονόματα αρχείων. Η εκτύπωση από το πακέτο ορίζει προσωρινά τον ίδιο τίτλο στη γονική σελίδα.

## v1.10.6
- Λίστα εργασιών πακέτου (site και admin): αφαιρέθηκε η στήλη «Διάρκεια» (`actual_minutes`). Μένουν Ημ/νία, Αίτημα, Χρεώσιμο, Κόστος και ενέργειες.

## v1.10.7
- Αναφορά πακέτου: `border-radius:10px` στις επικεφαλίδες `.sp-report-section h2` και `.sp-summary-heading`, όπως στις κάρτες εργασιών, τραπεζών και σύνοψης.

## v1.11.0
- Νέο `billing_mode` = `fixed` («Σταθερή τιμή ανά εργασία»).
  - Στο πλάνο, το `hourly_rate` κρατά την προτεινόμενη τιμή εργασίας (πεδίο `fixed_price`). Χωρίς ελάχιστη χρέωση και step.
  - Το πακέτο αντιγράφει την προτεινόμενη τιμή.
  - `entry.save`: `cost` = η τιμή που πληκτρολογήθηκε (`price`), `actual_minutes` = `billable_minutes` = προαιρετικός χρόνος.
- Οθόνη πακέτου: στατιστικά Εργασίες / Χρόνος / Συνολικό κόστος. Η φόρμα εργασίας έχει Τιμή και προαιρετικά λεπτά, και η επεξεργασία φορτώνει την τιμή.
- Αναφορά: περιγραφή για σταθερή τιμή, κάρτες Εργασίες και Συνολικό κόστος, στήλη «Τιμή», οικονομική σύνοψη (πλήθος εργασιών, καθαρή αξία, Φ.Π.Α., σύνολο) και τράπεζες.
- Λίστα πλάνων: τύπος «Σταθερή τιμή», τιμή «x € / εργασία».
- Σελιδοποίηση αναφοράς: το `.sp-page-content` δεν είναι πλέον flex και η σύνοψη δεν έχει `margin-top:auto`. Μετριέται με το πραγματικό της ύψος, οπότε δεν πηγαίνει άσκοπα σε νέα σελίδα ούτε πέφτει πάνω στο υποσέλιδο.

## v1.11.1
- Αναβάθμιση: το `plans.plan_type` γίνεται `varchar(190)`, αν είναι μικρότερο. Οι εκδόσεις 1.0.1 το δημιουργούσαν ως `varchar(20)`, με αποτέλεσμα «Data too long for column 'plan_type'». Ο έλεγχος διαβάζει το πλήρες `Type` με `getTableColumns(false)`.
- Πακέτα με πλάνο `fixed`: πεδία `package_title` / `package_description` στη φόρμα νέου πακέτου και στην επεξεργασία. Εμφανίζονται μόνο όταν το επιλεγμένο πλάνο είναι σταθερής τιμής και αποθηκεύονται ως `plan_type` / `plan_description` του πακέτου. Αν μείνουν κενά, χρησιμοποιούνται το όνομα και η περιγραφή του πλάνου.

## v1.11.2
- Φόρμα νέου πακέτου: αφαιρέθηκε η κλάση `sp-purchase-field` (φόντο και padding 10px) από την ημερομηνία αγοράς, ώστε να στοιχίζεται με τα υπόλοιπα πεδία.


## v1.12.0 — Πελάτες & Ειδοποιήσεις
- Νέο view `notify` (Service Hub → Πελάτες) με καρτέλες: Επισκόπηση, Πελάτες, Υπηρεσίες πελατών, Κατάλογος υπηρεσιών, Αναφορές maintenance, Αποστολές, Ιστορικό, Ρυθμίσεις email.
- Νέοι πίνακες: `#__supportplans_clients`, `#__supportplans_catalog`, `#__supportplans_client_services`, `#__supportplans_notify_reports`, `#__supportplans_notify_campaigns`, `#__supportplans_notify_messages`, `#__supportplans_login_attempts` (δημιουργούνται από το `script.php`).
- Κατάλογος: ελεύθερες υπηρεσίες χωρίς προκαθορισμένους τύπους, με `price`, `duration_months`, `reminder_days`, `auto_remind`. Ανάθεση σε πελάτη: αντίγραφο τιμής/διάρκειας, λήξη = έναρξη + διάρκεια αν μείνει κενή. Ημέρες υπενθύμισης: ανάθεση → κατάλογος → `notify_reminder_days`.
- `Helper\Notify`: ουρά/ιστορικό (`notify_messages`), μοναδικό `ref` ανά υπενθύμιση (`renewal:{service}:{expiry}:{offset}`) ώστε κάθε υπενθύμιση να στέλνεται μία φορά, catch-up με ένα μόνο μήνυμα, ακύρωση παλιών υπενθυμίσεων στην ανανέωση, atomic claim (`queued` → `sending`) ανά μήνυμα, έως 3 προσπάθειες, έλεγχοι πριν την αποστολή (ενεργός πελάτης, ίδιο email, ενεργή υπηρεσία, ίδια λήξη, απεγγραφή).
- Κείμενα email με την ίδια ελαφριά μορφοποίηση και τον ίδιο editor με τις Προσφορές (`OfferHelper::render`, escaped). HTML email με branding (logo, accent, στοιχεία επιχείρησης) και plain-text εναλλακτικό.
- Αποστολές: όλοι / πελάτες με υπηρεσία του καταλόγου / επιλεγμένοι, `List-Unsubscribe` + `List-Unsubscribe-Post` (RFC 8058). Δημόσιο `task=unsubscribe.run` με HMAC token (site secret)· απεγγραφή μόνο με POST.
- Task plugin `plg_task_supportplansnotify` (routine `supportplans.notify`), εγκαθίσταται από το `script.php` (το `installSystemPlugin` γενικεύτηκε σε `installPlugin($group, …)` με αντιγραφή υποφακέλων). Από CLI cron χρησιμοποιείται το τελευταίο URL του site (`notify_site_root`).
- Ασφάλεια σύνδεσης: `AuthCookie::allowed()` = `core.admin` στο `com_supportplans` (Super Users) σε login, cookie restore και σε κάθε αίτημα. Throttling στη βάση: 5 αποτυχίες ανά username ή 10 ανά IP σε 15 λεπτά. `login_lifetime` έως 24 ώρες.
- Ελέγχθηκε σε Joomla 6.2.0 (MariaDB, PHP 8.3): αναβάθμιση από 1.11.2, σύνδεση/OTP, όλες οι οθόνες, κατάλογος, πελάτες, CSV, αναθέσεις, υπενθυμίσεις (cron/CLI scheduler), αναφορές, αποστολές, απεγγραφή, throttling, CSRF.

## v1.13.0 — Συνδυαστικές υπενθυμίσεις, ΦΠΑ, διατύπωση ανά κατάσταση
- `Notify::candidates()` → `groups()`: ανά πελάτη, ταξινόμηση κατά λήξη· μια υπηρεσία μπαίνει στην ομάδα αν λήγει έως `notify_group_days` (προεπιλογή 7) μετά την προηγούμενη. Υποψήφιες είναι οι ενεργές υπηρεσίες με `auto_remind` που δεν έχουν περάσει την τελευταία δική τους ημέρα υπενθύμισης.
- `groupOffset()`: ημέρα υπενθύμισης που έχει έρθει, μετρημένη από την πρώτη λήξη της ομάδας, με την ένωση των ημερών όλων των υπηρεσιών της.
- Νέος πίνακας `#__supportplans_notify_sent` (service_id, expiry_date, offset_days, message_id): οι markers διεκδικούνται με `INSERT IGNORE` πριν μπει το μήνυμα στην ουρά (παράλληλες εκτελέσεις δεν στέλνουν διπλά). Το μήνυμα κρατά `service_ids` = `id:λήξη,…`· αν πριν την αποστολή κάποια υπηρεσία ανανεώθηκε ή απενεργοποιήθηκε, ακυρώνεται και οι markers του ελευθερώνονται, ώστε η επόμενη εκτέλεση να ξαναφτιάξει την ομάδα. Οι υπενθυμίσεις της 1.12.0 (`ref` renewal:…) μεταφέρονται σε markers στην αναβάθμιση.
- `buildRenewal($services, $client)`: `{services_status}` (εισαγωγή μίας/πολλών υπηρεσιών και διατύπωση `notify_phrase_before|today|after` ανά υπηρεσία), `{expired_notice}` (`catalog.expired_text` ή `notify_expired_text`), `{price_table}` (Οικονομική σύνοψη όπως στις προσφορές, τιμές προ ΦΠΑ, `notify_vat_rate`), `{banks}` (`OfferHelper::banks()`). Θέμα: `notify_renewal_subject` / `notify_subject_today` / `notify_subject_after` κατά την πιο επείγουσα υπηρεσία.
- Τα προεπιλεγμένα κείμενα υπενθύμισης της 1.12.0 αντικαθίστανται από τα νέα, αν δεν είχαν αλλάξει.
- Ελέγχθηκε σε Joomla 6.2.0: αναβάθμιση 1.12.0 → 1.13.0, προσομοίωση 40 ημερών με καθημερινή εκτέλεση (συνδυαστικά στις 30/14/7/1 ημέρες, ανανέωση μίας υπηρεσίας στη μέση χωρίς διπλά μηνύματα, «λήγει σήμερα», «έληξε» με κείμενο διακοπής), πίνακας τιμών/ΦΠΑ και τράπεζες.

## v1.14.0 — Excel
- `Helper\Xlsx`: ανάγνωση/δημιουργία .xlsx χωρίς εξωτερικές βιβλιοθήκες (ZipArchive + SimpleXML). Ανάγνωση: φύλλο «Πελάτες» ή το πρώτο, shared/inline strings, ημερομηνίες Excel (serial) ή κειμένου· χωρίς DTD, όριο 20 MB ανά XML. Δημιουργία: bold header, frozen πρώτη γραμμή, πλάτη στηλών, στήλη ημερομηνίας dd/mm/yyyy, λίστα επιλογής (data validation).
- `notify.importSheet` (POST + token): .xlsx ή .csv έως 5 MB. Στήλες κατά επικεφαλίδα, ελληνικές ή αγγλικές (`email`, `name`, `company`, `service`, `label`, `expiry_date`, `price`, `duration_months`). Μία γραμμή ανά υπηρεσία πελάτη· ίδιο email = ίδιος πελάτης, ίδιος πελάτης + υπηρεσία + domain = ενημέρωση. Αλλαγή λήξης ακυρώνει τις εκκρεμείς υπενθυμίσεις της παλιάς ημερομηνίας. Λάθη με τον αριθμό γραμμής του αρχείου.
- `notify.template` / `notify.export`: πρότυπο (κενό) και εξαγωγή υπαρχόντων, με φύλλα «Πελάτες» (λίστα από τον κατάλογο), «Κατάλογος», «Οδηγίες». Μόνο για συνδεδεμένους χρήστες του Service Hub.
- Αφαιρέθηκε το `clients.phone` (DROP COLUMN στην αναβάθμιση) και από τις οθόνες.
- Ελέγχθηκε σε Joomla 6.2.0 με αρχείο γραμμένο από openpyxl (ημερομηνίες ως ημερομηνίες και ως κείμενο, τιμές «15,50»), επανεισαγωγή χωρίς διπλές εγγραφές, CSV με ελληνικές επικεφαλίδες, άνοιγμα των παραγόμενων αρχείων σε LibreOffice.

## v1.15.0 — Πληρώθηκε
- `Notify::renew($id, $newExpiry, $paidOn, $amount, $note)`: νέα λήξη (προεπιλογή: τρέχουσα λήξη + διάρκεια, `nextExpiry()`), `status=active`, ακύρωση όλων των εκκρεμών υπενθυμίσεων της υπηρεσίας (και συνδυαστικών· οι υπόλοιπες υπηρεσίες της ομάδας ξαναπρογραμματίζονται), εγγραφή στον νέο πίνακα `#__supportplans_renewals` (old/new expiry, paid_on, amount, note, created_by).
- `notify.renew` (φόρμα «Πληρώθηκε / Ανανέωση» στη σελίδα της υπηρεσίας· η νέα λήξη πρέπει να είναι μετά την τρέχουσα) και `notify.renewGroup` («Πληρώθηκαν όλες» στις ομάδες υπενθυμίσεων του πελάτη· μόνο υπηρεσίες του ίδιου πελάτη).
- Η διαγραφή υπηρεσίας ακυρώνει και τις συνδυαστικές υπενθυμίσεις που την περιέχουν και σβήνει το ιστορικό της.

## v1.16.0 — Χειροκίνητη επιλογή υπηρεσιών, συμπαγή email, λογότυπο
- `notify.remindNow` με `ids[]` + `client_id`: ένα μήνυμα για ακριβώς τις υπηρεσίες που επιλέχθηκαν (`Notify::queueRenewalFor()`). Κάρτα «Χειροκίνητη υπενθύμιση» στη σελίδα του πελάτη με checkboxes (`Notify::remindable()`), προεπιλογή την ομάδα της υπηρεσίας (`?remind=ID`) ή την πρώτη ομάδα, προεπισκόπηση με POST `rp_ids[]` στο view.
- Email: `priceTable()` 2 στήλες + γραμμές συνόλων, χωρίς επαναληπτική πρόταση· `banksTable()` μία γραμμή ανά τράπεζα, κοινός δικαιούχος στον τίτλο, `ibanHtml()` τετράδες εναλλάξ bold· `expiredNotice()` ενώνει υπηρεσίες με το ίδιο κείμενο.
- `wrap()`: λογότυπο πάνω δεξιά από `Notify::emailLogoUrl()` (`notify_email_logo` ή PNG/JPG/GIF `brand_logo`). Ρυθμίσεις email: δημιουργία PNG από το SVG στον browser (canvas, 88px ύψος) ή ανέβασμα· ο server το επανακωδικοποιεί με GD σε `images/supportplans/email-logo.png` (≤1 MB, ≤1200×600).
- Διόρθωση διπλής κωδικοποίησης (`&amp;amp;`) στα `action` των φορμών του view `notify` (`OfferHelper::url(…, false)` μέσα σε escape). Ελέγχθηκε με Playwright που πατά τα κουμπιά: Υπενθύμιση…/προεπισκόπηση/αποστολή, Πληρώθηκε, Πληρώθηκαν όλες, Έλεγχος λήξεων, Διαγραφή πελάτη, πρότυπο & εισαγωγή Excel, δοκιμαστικό, λογότυπο.

## v1.16.1
- `banksTable()`: 3 στήλες (τράπεζα | IBAN | δικαιούχος) σε μεγάλες οθόνες· με `@media (max-width:520px)` στο `<style>` του email τα κελιά γίνονται `display:block`, οπότε το IBAN (nowrap) είναι σε δική του γραμμή. Κάτω από 380px μικραίνουν τα περιθώρια (`.sp-n-outer`, `.sp-n-body`) και η γραμματοσειρά του IBAN, ώστε να χωρά και σε 320px. Προγράμματα χωρίς media queries (π.χ. Outlook desktop) κρατούν τις 3 στήλες.

## v1.16.2 — Outlook & γραμματοσειρές
- Το Outlook για Windows (μηχανή Word) κάνει το κείμενο κάθε κελιού παράγραφο, οπότε ο γενικός κανόνας `p{margin}` μεγάλωνε τις γραμμές των πινάκων. Τώρα τα περιθώρια μπαίνουν inline μόνο στα μπλοκ του κειμένου (`inlineBlocks()`), κάθε `td` παίρνει `font-family` (`cellFonts()`), οι κάρτες έχουν `line-height` σε px με `mso-line-height-rule:exactly`, και υπάρχει `<!--[if mso]>` με `border-collapse`, μηδενικά περιθώρια για `.MsoNormal` και πίνακα 640px.
- `notify_font` (Google Fonts με ελληνικά: Geom, Inter, Roboto, Open Sans, Noto Sans ή Arial)· φορτώνεται μόνο εκτός Outlook (`<!--[if !mso]>`). `notify_font_outlook` (Aptos, Calibri, Arial) μόνο για Outlook για Windows.
- Λογότυπο με `width`/`height` από τις διαστάσεις του αρχείου (`emailLogo()`).

## v1.16.3
- `renderText()`: όταν ένα block (`{price_table}`, `{banks}`, `{expired_notice}`, `{services_status}` …) βρίσκεται σε παράγραφο μαζί με άλλο κείμενο (μονή αλλαγή γραμμής στο πρότυπο → `<br>`), η παράγραφος σπάει γύρω του. Το Outlook για Windows έδινε στα κελιά των πινάκων μέσα σε `<p>` τα περιθώρια της παραγράφου.
- Ετικέτες συνόλων στοιχισμένες αριστερά.

## v1.16.4
- `outlookCells()`: σε κάθε `table.sp-n-data` το περιεχόμενο κάθε κελιού τυλίγεται σε `<p class="sp-n-cell">` με `margin:0`, `mso-margin-top-alt:0`, `mso-margin-bottom-alt:0`, ακριβές `line-height` και τις ιδιότητες κειμένου του κελιού· προστίθεται `valign`. Στόχος το «auto» spacing πριν/μετά που προσθέτει το Word engine του Outlook για Windows στις αυτόματες παραγράφους των κελιών.

## v1.17.0 — Αναίρεση πληρωμής
- `Notify::undoRenewal($id)`: μόνο η τελευταία εγγραφή `#__supportplans_renewals` της υπηρεσίας και μόνο αν η τρέχουσα λήξη = `new_expiry`. Επαναφέρει `old_expiry` και `old_status` (νέα στήλη, αποθηκεύεται από το `renew()`), σβήνει την εγγραφή, ακυρώνει ό,τι είναι στην ουρά για την υπηρεσία. Οι markers των υπενθυμίσεων της παλιάς λήξης μένουν, οπότε δεν ξαναστέλνονται.
- `notify.undoRenewal` (POST + token). Κουμπιά «Αναίρεση» στο ιστορικό της υπηρεσίας και στην κάρτα «Πρόσφατες πληρωμές» του πελάτη (`clientRenewals()`, `canUndo()`).

## v1.17.1
- `servicesQuery()`: `last_paid_on`, `last_old_expiry`, `last_new_expiry` από την τελευταία εγγραφή `renewals`· η λίστα δείχνει «με Πληρώθηκε … από …» όταν η τρέχουσα λήξη είναι αυτή της πληρωμής.
- Φόρμα ανάθεσης: η λήξη δεν συμπληρώνεται πια αυτόματα από έναρξη + διάρκεια (κίνδυνος να αποθηκευτεί υπολογισμένη ημερομηνία αντί της πραγματικής)· μόνο ένδειξη. Η έναρξη είναι κενή εξ ορισμού.

## v1.17.2
- Φόρμα υπηρεσίας πελάτη: ζωντανός έλεγχος λήξης (ημέρα εβδομάδας, ημερομηνία, ημέρες από σήμερα, προειδοποίηση > 365 ημέρες).
- Κάρτα «Πληρώθηκε / Ανανέωση»: το ιστορικό εμφανίζεται πάντα, με εξήγηση όταν δεν υπάρχει πληρωμή ή η αναίρεση δεν επιτρέπεται.
- Αιτία των λάθος ετών: μέχρι την 1.17.0 η φόρμα ανάθεσης έβαζε αυτόματα λήξη = σήμερα + διάρκεια· αλλάζοντας μόνο ημέρα/μήνα στο πεδίο ημερομηνίας, το έτος έμενε +1/+2. Καταργήθηκε στην 1.17.1.

## v1.18.0 — Ιστορικό ενεργειών, Client Management
- `Helper\Activity` + πίνακας `#__supportplans_activity`. Ο site `Dispatcher` καλεί `Activity::begin($task)` πριν από κάθε task και `finish()` στο τέλος του αιτήματος (shutdown, ώστε να γράφεται και μετά από redirect· σε exception με το σφάλμα). Ενότητα και ελληνική περιγραφή ανά task, όνομα στοιχείου από το `id` (πακέτο, εργασία, πλάνο, προσφορά, template, πελάτης, υπηρεσία, αναφορά, αποστολή, μήνυμα), αποτέλεσμα από τα μηνύματα του Joomla. Μόνο allow-list πεδίων (ποτέ κωδικοί/OTP/κείμενα). Οι αυτόματες εκτελέσεις καταγράφονται όταν έκαναν κάτι.
- View `activity` (φίλτρα από/έως, ενότητα, χρήστης, αποτέλεσμα, αναζήτηση, σελίδες) και `activity.export` (Excel, έως 20.000 γραμμές, φύλλο «Φίλτρα»).
- `Helper\Sidebar`: κοινή πλοήγηση για τα νέα views· «Client Management» και «Ιστορικό ενεργειών» και στα παλιά templates.
- Πεδίο λήξης: info icon με tooltip (`.sp-label-tooltip`) αντί για κείμενο κάτω από το πεδίο· checkboxes στοιχισμένα· links στους πίνακες ίδιου μεγέθους, semibold.
- Σημείωση: καταγράφονται οι ενέργειες του Service Hub (site). Το Joomla Administrator του component δεν καταγράφεται.

## v1.19.0 — Back Office, συμπτυσσόμενο sidebar
- Όλα τα ορατά «Support Plans» έγιναν «Back Office» (manifest name και admin menu, plugins, task, login/OTP, email OTP, τίτλοι, οδηγίες). Το element παραμένει `com_supportplans`, ώστε η αναβάθμιση να κρατά δεδομένα και ρυθμίσεις· το `installPlugin()` ενημερώνει πλέον και το `name` των ήδη εγκατεστημένων plugins. Ένας τίτλος σελίδας που έχει οριστεί χειροκίνητα στις Ρυθμίσεις («page_title») δεν αλλάζει.
- `Sidebar::head()`: label + burger (`.sp-sb-toggle`) σε όλα τα templates του Service Hub. Κλάση `sp-sb-collapsed` στο `<html>` (εφαρμόζεται inline πριν το paint από `localStorage.spSidebar`), `sp-sb-anim` μόνο μετά από κλικ, ώστε το animation (πλάτος 248→78px, grid columns, opacity των labels, γραμμές του burger) να μη γίνεται σε κάθε φόρτωση. Tooltips (title) στα links. Κάτω από 900px το sidebar είναι ήδη συμπτυγμένο και το κουμπί κρύβεται.

## v1.20.0
- `Sidebar::head()`: λογότυπο (`OfferHelper::brand()`, λευκό με `filter:brightness(0) invert(1)`) και burger στην κορυφή του sidebar· κλάση `sp-sb-layout` στο `<html>`. Πάνω από 900px κρύβεται το `.sp-appbar`, το sidebar ξεκινά από `top:0` και το `.sp-app` χάνει το padding-top. Στο συμπτυγμένο sidebar η κεφαλίδα κρατά το ίδιο ύψος (40px), ώστε τα εικονίδια να μη μετακινούνται.
- Burger: γραμμές 1.5px, χωρίς background στο hover.
- `notify.css`: κελιά πινάκων, έντονα, links και φίλτρα στα 14px (όσο το κείμενο της περιγραφής), δευτερεύον κείμενο 12.5px.

## v1.21.0 — Λογότυπο sidebar
- Params `sidebar_logo` (διαδρομή/URL ή upload σε `images/supportplans/sidebar-logo.<ext>`, έλεγχος MIME με finfo, ≤ 2 MB) και `sidebar_logo_white`. `Sidebar::logo()`: το δικό του λογότυπο όπως είναι (ή λευκό αν επιλεγεί), αλλιώς το `brand_logo` σε λευκό. Προεπισκόπηση στο χρώμα του sidebar στις Ρυθμίσεις (και ζωντανά πριν την αποθήκευση).

## v1.21.1
- Geom σε `code/kbd/pre/samp` και σε όλα τα πεδία φορμών μέσα στο `.sp-saas-root`· αφαιρέθηκε το `font-monospace` από το πεδίο ενημερώσεων. Έλεγχος με Playwright σε 20 σελίδες + login/OTP: κάθε ορατό στοιχείο με κείμενο έχει πρώτη γραμματοσειρά Geom και το `document.fonts.check('16px Geom')` είναι true.

## v1.21.2
- `html body .sp-saas-root *` → Geom με !important (υψηλότερη εξειδίκευση από κανόνες template όπως `h2{font-family}` ακόμα και με !important)· σταθερά μεγέθη h1 29px, h2 20px, h3 16px, h4–h6 14px. Geom και στο `.sp-report`.
- Login/OTP: το URL `family=Geom:opsz,wght@9..72,300..800` επέστρεφε 400 από το Google Fonts (το Geom δεν έχει άξονα opsz), οπότε το Geom δεν φόρτωνε ποτέ· τώρα `ital,wght@0,300..900;1,300..900`. Το OTP δεν είχε καθόλου link για τη γραμματοσειρά.
- Έλεγχος με Playwright σε 13 σελίδες με «εχθρικό» CSS template (Times New Roman και 44px με !important σε h1–h6/κείμενα): όλα τα ορατά κείμενα Geom, το αρχείο Geom φορτωμένο (`FontFace.status === 'loaded'`, όχι `document.fonts.check`, που επιστρέφει true και για άγνωστη γραμματοσειρά).

## v1.22.0 — Ώρα αποστολής
- `notify_send_time` (HH:MM, προεπιλογή 09:30, κενό = οποιαδήποτε ώρα). `Notify::run($force)`: οι υπενθυμίσεις ανανέωσης ουρώνονται μόνο όταν `sendTimeReached()` (ώρα site ≥ ώρα αποστολής)· οι markers κρατούν μία ουρά ανά ημέρα/όριο. Η ουρά (αναφορές, αποστολές, retries) τρέχει πάντα. Το «Έλεγχος λήξεων & αποστολή τώρα» καλεί `run(true)`.
- Διόρθωση CLI: `cardStyles()`/`accent()` χρησιμοποιούσαν `OfferHelper::brand()`, που υπολογίζει το logo URL με `Uri::root()`· σε cron μέσω `cli/joomla.php` αυτό μπορεί να πετάξει «Could not parse the requested URI» και η εργασία τερμάτιζε με κωδικό 5. Νέο `Notify::brandColors()` μόνο από τα params· το `siteRoot()` πιάνει εξαίρεση του `Uri::root()`.
- Έλεγχος με τον Joomla scheduler (CLI): ώρα στο μέλλον → 0 υπενθυμίσεις, ώρα που πέρασε → 1, δεύτερη εκτέλεση ίδιας μέρας → 0, κενό → 1· κουμπί πριν την ώρα → αποστολή.

## v1.22.1
- `OfferHelper::upperGr()`: κεφαλαία χωρίς τόνους (Ά→Α, Έ→Ε, Ή→Η, Ί→Ι, Ό→Ο, Ύ→Υ, Ώ→Ω, ΐ→Ϊ, ΰ→Ϋ, και αποσυντεθειμένοι τόνοι), αντί για `mb_strtoupper()` που κρατά τους τόνους. Χρησιμοποιείται στο «Πελάτης» του εγγράφου προσφοράς, στα ονόματα τραπεζών (προσφορά, email) και στα αρχικά του λογοτύπου.

## v1.23.0 — Προτάσεις δομής
- Νέος πίνακας `#__supportplans_structures` (στοιχεία εγγράφου, κείμενα, `tree` ως JSON: επίπεδη λίστα `{title, level, purpose, content}`). Helper `Structure` (κανονικοποίηση επιπέδων: κάθε κόμβος έως ένα επίπεδο βαθύτερα από τον προηγούμενο, μέγιστο 5 επίπεδα· αρίθμηση, διαδρομές, γραμμές σύνδεσης δέντρου), `StructureController` (create/apply/save/preview/duplicate/delete με token), views `structures` (λίστα/νέα/επεξεργασία) και `structuredoc` (εκτυπώσιμο, μόνο για συνδεδεμένους).
- Editor δέντρου `media/js/structure-editor.js`: μετακίνηση/εσοχή μαζί με τις υποενότητες, Enter/Tab/Shift+Tab/Alt+↑↓, SpEditor ανά ενότητα (φορτώνει όταν ανοίγει), προεπισκόπηση με μη αποθηκευμένες αλλαγές.
- Pdf: ίδιο εξώφυλλο/header/footer με τις προσφορές, ο paginator σε `media/js/doc-paginate.js`· sitemap ως επίπεδη λίστα γραμμών με «ράγες» ανά επίπεδο, ώστε να σπάει σε οποιαδήποτε γραμμή μεταξύ σελίδων χωρίς να χάνονται οι γραμμές σύνδεσης (`media/css/structure.css`).
- Sidebar «Προτάσεις δομής», Ιστορικό ενεργειών (`structure.*`), Οδηγίες §10.

## v1.23.1
- Στήλες `intro|sitemap|content|outro` `_break` / `_space` στον πίνακα structures (μέσω `ensureSchema`, προεπιλογή: προτάσεις περιεχομένου σε νέα σελίδα). Κάθε κόμβος του δέντρου έχει `break` και `space` (κενό = αυτόματη απόσταση ανά επίπεδο). `Structure::layout()` δίνει `sp-o-break` ή `margin-top`.
- Τίτλοι προτάσεων περιεχομένου: αριθμός = αριθμός ενότητας εγγράφου + αριθμός κόμβου (3.1, 3.2.1), μεγέθη 11.6 / 10.8 / 10.3pt κάτω από το h2 (13pt).
- Διόρθωση: `(object) [...] + array` στο `collect()`.

## v1.24.0 — Έγγραφα, κοινή αρίθμηση, αποστολή προσφορών
- `Helper\Documents`: μητρώο εγγράφων (πακέτα `form_number`, προσφορές `offer_number`, προτάσεις δομής `number`) και `nextNumber()` κοινό για όλα: μέγιστο αριθμητικό τμήμα + 1 (κρατά πρόθεμα και μηδενικά), νέο έτος → `yy001` για αριθμούς μορφής yy+3. `OfferHelper::nextNumber()` / `Structure::nextNumber()` το χρησιμοποιούν· η φόρμα νέου πακέτου έχει πλέον προσυμπληρωμένο «Αρ. εντύπου» (κενό → αυτόματα).
- View `documents` με φίλτρο τύπου, αναζήτηση, Pdf / Άνοιγμα.
- `Sidebar::nav()` με ομάδες: Πλάνα, Έγγραφα (Πακέτα, Προσφορές, Προτάσεις δομής), Client Management, Ρυθμίσεις, Ιστορικό, Οδηγίες· όλες οι σελίδες χρησιμοποιούν πλέον το ίδιο μενού (αφαιρέθηκαν τα στατικά).
- Αποστολή προσφοράς: `offer.send` (έως 10 παραλήπτες + cc, μήνυμα με markup/μεταβλητές μέσα στο `Notify::wrap`, σύνδεσμος `view=offerdoc&t=<96 hex>` και/ή συνημμένο Pdf έως 15 MB με έλεγχο `%PDF-`), `offer.revokelink`, πίνακας `#__supportplans_offer_sends`, στήλη `share_token` στις προσφορές. `Notify::senderMailer()` (αποστολέας/reply-to των ρυθμίσεων email). Ο σύνδεσμος είναι δημόσιος μόνο με έγκυρο token, με noindex/no-referrer/no-store.

## v1.25.0 — Αποστολή όλων των εγγράφων
- `Helper\DocMail`: κοινή αποστολή για `offer`, `structure`, `plan` (διάλογος, ιστορικό, σύνδεσμος, αποστολή), `DocmailController` (`docmail.send`, `docmail.revoke`)· αντικαθιστά τα `offer.send` / `offer.revokelink`. Στήλη `doc_type` στο `#__supportplans_offer_sends`, `share_token` στις προτάσεις δομής, δημόσιο `view=structuredoc&t=…`. Στα πακέτα ο σύνδεσμος είναι ο υπάρχων σύνδεσμος αναφοράς (`share.*`), η φόρμα email της κάρτας συνδέσμου αντικαταστάθηκε από τον κοινό διάλογο. `&send=1` ανοίγει τον διάλογο (κουμπί «✉ Email» στα Έγγραφα).

## v1.26.0 — Προστασία συνδέσμων, αποστολείς ανά είδος
- `Helper\DocGate`: λήξη (`share_expires`, 410) και OTP (`share_otp`, `share_emails`) για τους συνδέσμους `offerdoc` / `structuredoc` / `report`. Κωδικός 6 ψηφίων (password_hash στο session, 10΄, 5 προσπάθειες, 1 ανά λεπτό / 5 ανά ώρα) στο επιλεγμένο (masked) email· σωστός κωδικός → υπογεγραμμένο HMAC cookie (httponly, 12 ώρες) ανά σύνδεσμο. Δημόσια tasks `docgate.send` / `docgate.verify` με CSRF token. Η σελίδα του κωδικού δεν δείχνει τίποτα από το έγγραφο.
- Διάλογος αποστολής: «Προστασία με κωδικό μίας χρήσης», «Λήξη συνδέσμου»· οι ρυθμίσεις της αποστολής εφαρμόζονται στον σύνδεσμο του εγγράφου, οι παραλήπτες (to + cc) προστίθενται στους επιτρεπτούς.
- Αποστολείς ανά είδος: `doc_{offer|structure|plan}_{from_name|from_email|reply_to|bcc}`, `Notify::senderMailer($type)` / `Notify::senderOf($type)` (κενά → γενικός αποστολέας).
- Κουμπί email «bulletproof»: VML `v:roundrect` για Outlook Windows, padding στο κελί για τους υπόλοιπους.

## v1.26.1
- Κουμπί email: η απόσταση πάνω/κάτω από το κουμπί δίνεται με γραμμές πίνακα σταθερού ύψους (`mso-line-height-rule:exactly`), επειδή το Outlook αγνοεί το margin του `div` και το VML κολλούσε στο κείμενο.

## v1.26.2
- Πρότυπο Excel: η επικύρωση λίστας της στήλης «Υπηρεσία» είναι δυναμική (`OFFSET(...,COUNTA('Κατάλογος'!$A:$A)-1)`), ώστε να περιλαμβάνει γραμμές που προστίθενται στο φύλλο «Κατάλογος» μετά τη λήψη.

## v1.27.0 — Σύνοψη ανανεώσεων, {paid_notice}, {greeting}
- `Notify::summary($period, $from, $to)`: πληρωμές (`renewals.paid_on`, ποσό με ΦΠΑ ή τιμή υπηρεσίας × ΦΠΑ), προς πληρωμή (ενεργές υπηρεσίες ενεργών πελατών που λήγουν από σήμερα έως το τέλος της περιόδου), ληγμένες απλήρωτες· κάρτα στη σελίδα «Υπηρεσίες πελατών» (`sp`, `sf`, `st`).
- `Notify::alreadyReminded()`: υπάρχει ήδη μήνυμα ανανέωσης (sent/sending/queued) με το ίδιο `id:expiry` στο `service_ids` → block `{paid_notice}` (`notify_paid_notice` / `_multi`). Κείμενα χωρίς `{paid_notice}` το παίρνουν μετά το `{banks}`.
- `clients.salutation`, μεταβλητή `{greeting}` (του πελάτη ή `notify_greeting`), πεδίο στη φόρμα πελάτη και στήλη «Προσφώνηση» στο Excel. Μετάπτωση `Αγαπητέ/ή {client_name}` → `{greeting}` στα αποθηκευμένα κείμενα και στις πρόχειρες αποστολές.

## v1.28.0 — Χρήση ανά έτος
- `Helper\Year`: επιλεγμένο έτος (`?year=` → session, προεπιλογή το τρέχον), λίστα ετών με δεδομένα, επιλογέας στο `Sidebar::head()`. Φίλτρο έτους στις λίστες Εγγράφων, Προσφορών, Προτάσεων δομής, Πακέτων (στο τρέχον έτος και τα ενεργά παλαιότερα), στο Ιστορικό ενεργειών (προεπιλογή από/έως = έτος, και στην εξαγωγή Excel), στο ιστορικό email (`YEAR(created)`) και στη σύνοψη ανανεώσεων (παλιό έτος → όλο το έτος). Το άνοιγμα εγγράφου με id δεν φιλτράρεται.
- `{paid_notice}`: μετά τη λήξη `notify_paid_notice_after` («…επικοινωνήστε μαζί μας»), πριν / την ημέρα της λήξης από τη 2η υπενθύμιση το «…αγνοήστε…», στην 1η τίποτα.

