import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const translations = {
  et: {
    // Navigatsioon
    nav_dashboard:   'Töölaud',
    nav_devices:     'Seadmed',
    nav_borrow:      'Laenuta / Tagasta',
    nav_borrowings:  'Laenutused',
    nav_admin:       'Adminipaneel',
    nav_students:    'Õpilased',
    nav_signout:     'Logi välja',

    // Lehekülje pealkirjad
    title_dashboard:  'Töölaud',
    title_devices:    'Seadmete inventar',
    title_borrow:     'Laenuta / Tagasta seade',
    title_borrowings: 'Laenutuste ajalugu',
    title_admin:      'Adminipaneel',
    title_system:     'Seadmete süsteem',
    title_students:   'Õpilaste nimekiri',

    // Töölaud
    welcome_back:     'Tere tulemast tagasi',
    admin_desc:       'Admini töölaud — halda seadmeid ja jälgi laenutusi.',
    student_desc:     'Sirvi seadmeid ja laenutuste infot.',
    stat_total:       'Seadmeid kokku',
    stat_available:   'Saadaval',
    stat_borrowed:    'Laenutatud',
    stat_overdue:     'Tähtaeg ületatud',
    active_borrowings:'Aktiivsed laenutused',
    my_borrowings:    'Minu praegused laenutused',
    view_all:         'Vaata kõiki',
    no_borrowings:    'Aktiivseid laenutusi pole.',
    device:           'Seade',
    borrowed_by:      'Laenutaja',
    due_date:         'Tähtaeg',
    due_time:         'Tagastamise kellaaeg',
    status:           'Olek',

    // Sisselogimine
    sign_in:          'Logi sisse',
    sign_in_account:  'Logi oma kontole sisse',
    email:            'E-posti aadress',
    password:         'Parool',
    signing_in:       'Sisselogimine…',
    no_account:       'Pole kontot?',
    register:         'Registreeru',
    demo_credentials: 'Demo andmed',

    // Registreerimine
    create_account:   'Loo konto',
    join_system:      'Liitu seadmete süsteemiga',
    full_name:        'Täisnimi',
    confirm_password: 'Kinnita parool',
    creating:         'Konto loomine…',
    have_account:     'On juba konto?',

    // Seadmed
    search_devices:   'Otsi seadmeid…',
    filter_status:    'Filtreeri oleku järgi',
    filter_category:  'Filtreeri kategooria järgi',
    all_statuses:     'Kõik olekud',
    all_categories:   'Kõik kategooriad',
    available:        'Saadaval',
    borrowed:         'Laenutatud',
    maintenance:      'Hoolduses',
    no_devices:       'Seadmeid ei leitud.',
    borrow:           'Laenuta',
    return:           'Tagasta',
    category:         'Kategooria',
    serial:           'Seerianumber',
    send_notification:'Saada teavitus',
    notification_sent:'Teavitus saadetud',

    // Laenuta / Tagasta
    scan_barcode:     'Skanni vöökood või sisesta käsitsi',
    enter_barcode:    'Sisesta seadme vöökood',
    lookup:           'Otsi',
    device_info:      'Seadme info',
    name:             'Nimi',
    borrow_device:    'Laenuta seade',
    return_device:    'Tagasta seade',
    confirm_borrow:   'Kinnita laenutus',
    confirm_return:   'Kinnita tagastus',
    student_name:     'Õpilase nimi',
    student_email:    'Õpilase e-post',
    select_student:   'Vali õpilane…',
    return_time:      'Tagastamise kellaaeg',

    // Laenutused
    all_users:        'Kõik kasutajad',
    overdue:          'Tähtaeg ületatud',
    active:           'Aktiivne',
    returned:         'Tagastatud',
    return_date:      'Tagastamise kuupäev',
    actions:          'Tegevused',
    mark_returned:    'Märgi tagastatuks',
    change_due_date:  'Muuda tähtaega',

    // Admin
    add_device:       'Lisa seade',
    edit:             'Muuda',
    delete:           'Kustuta',
    save:             'Salvesta',
    cancel:           'Tühista',
    users:            'Kasutajad',
    role:             'Roll',
    admin:            'Admin',
    student:          'Õpilane',

    // Õpilased
    add_student:          'Lisa õpilane',
    import_students:      'Impordi õpilased',
    student_list:         'Õpilaste nimekiri',
    group:                'Rühm',
    delete_group:         'Kustuta rühm',
    borrowing_history:    'Laenutuste ajalugu',
    no_students:          'Õpilasi ei leitud.',
    student_added:        'Õpilane lisatud.',
    student_updated:      'Õpilane uuendatud.',
    student_deleted:      'Õpilane kustutatud.',
    confirm_delete_student: 'Kas oled kindel, et soovid selle õpilase kustutada?',
    confirm_delete_group: 'Kas oled kindel, et soovid kustutada kõik õpilased rühmast',
    active_borrowings_warning: 'Sellel õpilasel on veel aktiivseid laenutusi!',
    import_help:          'Lisa üks rida kohta: Nimi, E-post, Rühm (rühm on valikuline)',
    import_success:       'Imporditud',
    import_skipped:       'vahele jäetud (duplikaadid)',
    total_borrowings:     'Laenutusi kokku',
    returned_late:        'Tagastatud hilinenult',
    currently_out:        'Praegu väljas',
    close:                'Sulge',
    back:                 'Tagasi',

    // Oleku märgised
    status_available:   'Saadaval',
    status_borrowed:    'Laenutatud',
    status_maintenance: 'Hoolduses',
    status_overdue:     'Tähtaeg ületatud',
    status_returned:    'Tagastatud',
    status_active:      'Aktiivne',
  },
}

// English fallback = same keys
translations.en = { ...translations.et }

export const useI18nStore = defineStore('i18n', () => {
  // Default: Estonian
  const locale = ref(localStorage.getItem('locale') || 'et')

  function setLocale(lang) {
    locale.value = lang
    localStorage.setItem('locale', lang)
  }

  function toggleLocale() {
    setLocale(locale.value === 'en' ? 'et' : 'en')
  }

  const t = computed(() => (key) => {
    return translations[locale.value]?.[key] ?? translations['et']?.[key] ?? key
  })

  const isEstonian = computed(() => locale.value === 'et')

  return { locale, setLocale, toggleLocale, t, isEstonian }
})
