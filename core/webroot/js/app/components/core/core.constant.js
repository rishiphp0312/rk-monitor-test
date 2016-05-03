appConfig.getModule()
.constant('AUTH_EVENTS', {
    loginSuccess: 'auth-login-success',
    loginFailed: 'auth-login-failed',
    logoutSuccess: 'auth-logout-success',
    sessionTimeout: 'auth-session-timeout',
    notAuthenticated: 'auth-not-authenticated',
    notAuthorized: 'auth-not-authorized',
    setCurrentUser: 'set-current-user'
})
.constant('SERVICE_CALL', {
    system: {
        login: 100,
        logout: 101,
        checkSessionDetails: 104
    },
    userManagement: {
        forgotPassword: 102,
        confirmPassword: 103
    },
    translations: {
        getLanguageList: 2008,
        getLanguageKeys: 2002,
        deleteLanguageKey: 2003,
        getTranslationDetails: 2001,
        addTranslation: 2000,
        modifyTranslation:2004,
        compileLanguage: 2005,
        importFile: 2007,
        getExportUrl: 2006,
        getTranslationStrings: 2009,
        getDefaultLanguage: 2444
    }
})
.constant('ERROR_CODE', {
})
.constant('TIMEPERIOD_FORMAT', {
    'yyyy': 'yyyy',
    'yyyy.mm': 'yyyy.mm',
    'yyyy.mm.dd': 'yyyy.mm.dd',
    'yyyy-yyyy': 'yyyy-yyyy',
    'yyyy.mm-yyyy.mm': 'yyyy.mm-yyyy.mm',
    'yyyy.mm.dd-yyyy.mm.dd': 'yyyy.mm.dd-yyyy.mm.dd',
    'Qn:yyyy': 'Qn.yyyy'
})
.constant('CALENDER_FORMAT', {
    'yyyy-MM-dd': 'yyyy-MM-dd',
    'dd-MMMM-yyyy': 'dd-MMMM-yyyy',
    'yyyy/MM/dd': 'yyyy/MM/dd',
    'dd.MM.yyyy': 'dd.MM.yyyy',
    'shortDate': 'shortDate'
})
.constant('CALENDER_DATE_OPT', {
    formatYear: 'yyyy',
    startingDay: 1
})
.constant('PAGINATION_DEFAULT', {
    pageSize: 10,
    currentPage: 1,
    from: 1,
    to: 10,
    totalCount: '',
    pageSizes: [10, 20, 30, 40, 50]
})
.constant('MODULES', {
    translation: "translation"
})
