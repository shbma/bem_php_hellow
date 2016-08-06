module.exports = {
    block : 'page',
    title : 'Привет Мир!',
    head : [
        { elem : 'css', url : 'hello.min.css' }
    ],
    scripts : [{ elem : 'js', url : 'hello.min.js' }],
    content : [
        {
            block: 'hello',
            content: [
                {
                    elem: 'greeting',
                    content: 'Привет всем!'
                },
                {
                    block : 'input',
                    mods : { theme : 'islands', size : 'm' },

                    //подмешиваем элемент для добавления CSS-правил
                    mix : {block: 'hello', elem: 'input'},
                    placeholder : 'Введите имя'
                },
                {
                    block : 'button',
                    mods : { theme : 'islands', size : 'm', type : 'submit' },
                    text : 'Я отправляю данные'
                }
            ]
        },
    ]


}