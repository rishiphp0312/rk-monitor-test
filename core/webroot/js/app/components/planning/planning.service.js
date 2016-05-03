appConfig.getModule('planning')
.factory('planningService', [
function () {

    var planningService = {};

    var resultMatrix = [{
        id: '1',
        level: 1,
        levelName: 'Result Area',
        name: 'PROSPERITY',
        parentId: ''
    }, {
        id: '2',
        level: 1,
        levelName: 'Result Area',
        name: 'PEOPLE',
        parentId: ''
    }, {
        id: '3',
        level: 1,
        levelName: 'Result Area',
        name: 'PEACE',
        parentId: ''
    }, {
        id: '4',
        level: 1,
        levelName: 'Result Area',
        name: 'PLANET',
        parentId: ''
    }, {
        id: '1.1',
        level: 2,
        levelName: 'Outcome',
        name: 'Vulnerable populations are more food secure and better nourished',
        parentId: '1'
    }, {
        id: '1.2',
        level: 2,
        levelName: 'Outcome',
        name: 'Poor people benefit equitably from sustainable economic transformation',
        parentId: '1'
    }, {
        id: '2.1',
        level: 2,
        levelName: 'Outcome',
        name: 'Children, youth and adults benefit from an inclusive end equitable quality education system',
        parentId: '2'
    }, {
        id: '2.2',
        level: 2,
        levelName: 'Outcome',
        name: 'Disadvantaged women and girls benefit from comprehensive policies, norms and practices that guarantee their human rights',
        parentId: '2'
    }, {
        id: '2.3',
        level: 2,
        levelName: 'Outcome',
        name: 'Poor and most vulnerable people benefit from a more effective system of social protection',
        parentId: '2'
    }, {
        id: '2.4',
        level: 2,
        levelName: 'Outcome',
        name: 'People equitably access and use quality health, water and sanitation services',
        parentId: '2'
    }, {
        id: '2.5',
        level: 2,
        levelName: 'Outcome',
        name: 'Adolescents and youth actively engaged in decisions that affect their lives, health, well-being and development opportunities',
        parentId: '2'
    }, {
        id: '3.1',
        level: 2,
        levelName: 'Outcome',
        name: 'All people benefit from democratic and transparent governance institutions and systems that guarantee peace consolidation, human rights and equitable service delivery',
        parentId: '3'
    }, {
        id: '4.1',
        level: 2,
        levelName: 'Outcome',
        name: 'Most vulnerable people in Mozambique benefit from inclusive, equitable and sustainable management of natural resources and the environment',
        parentId: '4'
    }, {
        id: '4.2',
        level: 2,
        levelName: 'Outcome',
        name: 'Communities are more resilient to the impact of climate change and disasters',
        parentId: '4'
    }, {
        id: '1.1.0.1',
        level: 3,
        levelName: 'Indicator',
        name: '% of households with chronic food insecurity ',
        parentId: '1.1'
    }, {
        id: '1.1.0.2',
        level: 3,
        levelName: 'Indicator',
        name: '% of households with adequate food consumption',
        parentId: '1.1'
    }, {
        id: '1.1.0.3',
        level: 3,
        levelName: 'Indicator',
        name: 'Prevalence of chronic malnutrition amongst children under five years',
        parentId: '1.1'
    }, {
        id: '1.1.1',
        level: 3,
        levelName: 'Output',
        name: 'Government and stakeholders\' ownership and capacity strengthened to design and implement evidence-based food and nutrition policies',
        parentId: '1.1'
    }, {
        id: '1.1.2',
        level: 3,
        levelName: 'Output',
        name: 'Producers in agriculture and fisheries sectors with enhanced capacity to adopt sustainable production techniques for own consumption and markets ',
        parentId: '1.1'
    }, {
        id: '1.1.3',
        level: 3,
        levelName: 'Output',
        name: 'Public and private sectors invest in resilient, efficient and nutrition sensitive food systems ',
        parentId: '1.1'
    }, {
        id: '1.1.4',
        level: 3,
        levelName: 'Output',
        name: 'Communities (and women in particular) acquire the knowledge to adopt appropriate practices and behaviors to reduce chronic undernutrition',
        parentId: '1.1'
    }, {
        id: '1.1.1.1',
        level: 4,
        levelName: 'Indicator',
        name: 'No. of provinces where food fortification initiatives are  implemented ',
        parentId: '1.1.1'
    }, {
        id: '1.1.1.2',
        level: 4,
        levelName: 'Indicator',
        name: 'Agriculture Law',
        parentId: '1.1.1'
    }, {
        id: '1.1.1.3',
        level: 4,
        levelName: 'Indicator',
        name: 'No. of district economic and social plans (PESOD) in selected provinces that incorporate a gender sensitive FNS approach and specific FSN interventions',
        parentId: '1.1.1'
    }, {
        id: '1.1.1.4',
        level: 4,
        levelName: 'Indicator',
        name: 'No. of FSN assessments using gender lens supported at national level',
        parentId: '1.1.1'
    }];

    var indicatorData = [
        {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2016',
            value: {
                actual: 65,
                planned: 68
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Total'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2016',
            value: {
                actual: 70,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2016',
            value: {
                actual: 80,
                planned: 80
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ002',
            areaName: 'District 2',
            timePeriod: '2016',
            value: {
                actual: 55,
                planned: 50
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Total'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ002',
            areaName: 'District 2',
            timePeriod: '2016',
            value: {
                actual: 60,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2016',
            value: {
                actual: 72,
                planned: 80
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2016',
            value: {
                actual: 74,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2016',
            value: {
                actual: 75,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2016',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2017',
            value: {
                actual: 65,
                planned: 68
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Total'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2017',
            value: {
                actual: 70,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2017',
            value: {
                actual: 80,
                planned: 80
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ002',
            areaName: 'District 2',
            timePeriod: '2017',
            value: {
                actual: 55,
                planned: 50
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Total'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ002',
            areaName: 'District 2',
            timePeriod: '2017',
            value: {
                actual: 60,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2017',
            value: {
                actual: 72,
                planned: 80
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2017',
            value: {
                actual: 74,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2017',
            value: {
                actual: 75,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2017',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2018',
            value: {
                actual: 65,
                planned: 68
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Total'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2018',
            value: {
                actual: 70,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ001',
            areaName: 'District 1',
            timePeriod: '2018',
            value: {
                actual: 80,
                planned: 80
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ002',
            areaName: 'District 2',
            timePeriod: '2018',
            value: {
                actual: 55,
                planned: 50
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Total'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ002',
            areaName: 'District 2',
            timePeriod: '2018',
            value: {
                actual: 60,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2018',
            value: {
                actual: 72,
                planned: 80
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Female'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2018',
            value: {
                actual: 74,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Male'
        }, {
            indicator: 'Net Enrolment ratio in grade 1 of EP1',
            unit: 'Ratio',
            areaId: 'MOZ003',
            areaName: 'District 3',
            timePeriod: '2018',
            value: {
                actual: 75,
                planned: 75
            },
            source: 'MOZ MOE_EMIS_2018',
            subgroup: 'Female'
        }
    ]

    planningService.getResultMatrix = function () {
        return resultMatrix;
    }

    planningService.getPlanningData = function (planningId) {

        var data = '';

        angular.forEach(resultMatrix, function (resultMatrixObj) {
            if (resultMatrixObj.id == planningId) {
                data = resultMatrixObj;
            }
        })

        return data;
    }

    planningService.getIndicatoData = function () {
        return indicatorData;
    }

    return planningService;

} ])