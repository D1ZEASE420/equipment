import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const translations = {
  en: {
    // Navigation
    nav_dashboard: 'Dashboard',
    nav_devices: 'Devices',
    nav_borrow: 'Borrow / Return',
    nav_borrowings: 'Borrowings',
    nav_admin: 'Admin Panel',
    nav_signout: 'Sign out',

    // Page titles
    title_dashboard: 'Dashboard',
    title_devices: 'Device Inventory',
    title_borrow: 'Borrow / Return Device',
    title_borrowings: 'Borrowing History',
    title_admin: 'Admin Panel',
    title_system: 'Equipment System',

    // Dashboard
    welcome_back: 'Welcome back',
    admin_desc: 'Admin dashboard — manage devices and monitor borrowings.',
    student_desc: 'Browse devices and borrow equipment using your student account.',
    stat_total: 'Total Devices',
    stat_available: 'Available',
    stat_borrowed: 'Borrowed',
    stat_overdue: 'Overdue',
    active_borrowings: 'Active Borrowings',
    my_borrowings: 'My Current Borrowings',
    view_all: 'View all',
    no_borrowings: 'No active borrowings.',
    device: 'Device',
    borrowed_by: 'Borrowed by',
    due_date: 'Due date',
    status: 'Status',

    // Login
    sign_in: 'Sign in',
    sign_in_account: 'Sign in to your account',
    email: 'Email address',
    password: 'Password',
    signing_in: 'Signing in…',
    no_account: "Don't have an account?",
    register: 'Register',
    demo_credentials: 'Demo credentials',

    // Register
    create_account: 'Create account',
    join_system: 'Join the equipment system',
    full_name: 'Full name',
    confirm_password: 'Confirm password',
    creating: 'Creating account…',
    have_account: 'Already have an account?',

    // Devices
    search_devices: 'Search devices…',
    filter_status: 'Filter by status',
    all_statuses: 'All statuses',
    available: 'Available',
    borrowed: 'Borrowed',
    maintenance: 'Maintenance',
    no_devices: 'No devices found.',
    borrow: 'Borrow',
    category: 'Category',
    serial: 'Serial number',

    // Borrow / Return
    scan_barcode: 'Scan barcode or enter manually',
    enter_barcode: 'Enter device barcode',
    lookup: 'Look up',
    device_info: 'Device Info',
    name: 'Name',
    borrow_device: 'Borrow Device',
    return_device: 'Return Device',
    due_in_days: 'Due in (days)',
    confirm_borrow: 'Confirm Borrow',
    confirm_return: 'Confirm Return',

    // Borrowings
    all_users: 'All users',
    overdue: 'Overdue',
    active: 'Active',
    returned: 'Returned',
    return_date: 'Return date',
    actions: 'Actions',
    mark_returned: 'Mark returned',

    // Admin
    add_device: 'Add Device',
    edit: 'Edit',
    delete: 'Delete',
    save: 'Save',
    cancel: 'Cancel',
    users: 'Users',
    role: 'Role',
    admin: 'Admin',
    student: 'Student',

    // Status badges
    status_available: 'Available',
    status_borrowed: 'Borrowed',
    status_maintenance: 'Maintenance',
    status_overdue: 'Overdue',
    status_returned: 'Returned',
    status_active: 'Active',
  },

  et: {
    // Navigatsioon
    nav_dashboard: 'Töölaud',
    nav_devices: 'Seadmed',
    nav_borrow: 'Laenuta / Tagasta',
    nav_borrowings: 'Laenutused',
    nav_admin: 'Adminipaneel',
    nav_signout: 'Logi välja',

    // Lehekülje pealkirjad
    title_dashboard: 'Töölaud',
    title_devices: 'Seadmete inventar',
    title_borrow: 'Laenuta / Tagasta seade',
    title_borrowings: 'Laenutuste ajalugu',
    title_admin: 'Adminipaneel',
    title_system: 'Seadmete süsteem',

    // Töölaud
    welcome_back: 'Tere tulemast tagasi',
    admin_desc: 'Admini töölaud — halda seadmeid ja jälgi laenutusi.',
    student_desc: 'Sirvi seadmeid ja laenuta varustust oma õpilasekontoga.',
    stat_total: 'Seadmeid kokku',
    stat_available: 'Saadaval',
    stat_borrowed: 'Laenutatud',
    stat_overdue: 'Tähtaeg ületatud',
    active_borrowings: 'Aktiivsed laenutused',
    my_borrowings: 'Minu praegused laenutused',
    view_all: 'Vaata kõiki',
    no_borrowings: 'Aktiivseid laenutusi pole.',
    device: 'Seade',
    borrowed_by: 'Laenutaja',
    due_date: 'Tähtaeg',
    status: 'Olek',

    // Sisselogimine
    sign_in: 'Logi sisse',
    sign_in_account: 'Logi oma kontole sisse',
    email: 'E-posti aadress',
    password: 'Parool',
    signing_in: 'Sisselogimine…',
    no_account: 'Pole kontot?',
    register: 'Registreeru',
    demo_credentials: 'Demo andmed',

    // Registreerimine
    create_account: 'Loo konto',
    join_system: 'Liitu seadmete süsteemiga',
    full_name: 'Täisnimi',
    confirm_password: 'Kinnita parool',
    creating: 'Konto loomine…',
    have_account: 'On juba konto?',

    // Seadmed
    search_devices: 'Otsi seadmeid…',
    filter_status: 'Filtreeri oleku järgi',
    all_statuses: 'Kõik olekud',
    available: 'Saadaval',
    borrowed: 'Laenutatud',
    maintenance: 'Hoolduses',
    no_devices: 'Seadmeid ei leitud.',
    borrow: 'Laenuta',
    category: 'Kategooria',
    serial: 'Seerianumber',

    // Laenuta / Tagasta
    scan_barcode: 'Skanni vöökood või sisesta käsitsi',
    enter_barcode: 'Sisesta seadme vöökood',
    lookup: 'Otsi',
    device_info: 'Seadme info',
    name: 'Nimi',
    borrow_device: 'Laenuta seade',
    return_device: 'Tagasta seade',
    due_in_days: 'Tähtaeg (päevades)',
    confirm_borrow: 'Kinnita laenutus',
    confirm_return: 'Kinnita tagastus',

    // Laenutused
    all_users: 'Kõik kasutajad',
    overdue: 'Tähtaeg ületatud',
    active: 'Aktiivne',
    returned: 'Tagastatud',
    return_date: 'Tagastamise kuupäev',
    actions: 'Tegevused',
    mark_returned: 'Märgi tagastatuks',

    // Admin
    add_device: 'Lisa seade',
    edit: 'Muuda',
    delete: 'Kustuta',
    save: 'Salvesta',
    cancel: 'Tühista',
    users: 'Kasutajad',
    role: 'Roll',
    admin: 'Admin',
    student: 'Õpilane',

    // Oleku märgised
    status_available: 'Saadaval',
    status_borrowed: 'Laenutatud',
    status_maintenance: 'Hoolduses',
    status_overdue: 'Tähtaeg ületatud',
    status_returned: 'Tagastatud',
    status_active: 'Aktiivne',
  }
}

export const useI18nStore = defineStore('i18n', () => {
  const locale = ref(localStorage.getItem('locale') || 'en')

  function setLocale(lang) {
    locale.value = lang
    localStorage.setItem('locale', lang)
  }

  function toggleLocale() {
    setLocale(locale.value === 'en' ? 'et' : 'en')
  }

  const t = computed(() => (key) => {
    return translations[locale.value]?.[key] ?? translations['en']?.[key] ?? key
  })

  const isEstonian = computed(() => locale.value === 'et')

  return { locale, setLocale, toggleLocale, t, isEstonian }
})