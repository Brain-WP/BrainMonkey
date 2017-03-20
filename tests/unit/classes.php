<?php
namespace { // global code
    class MyClass
    {
        function returnSomething(){}
        function returnSomethingElse(){}
        function doSomething(){}
        function echoSomething(){}
        function aliasMe(){}
        function notMocked(){}
        function returnNull(){}
    }
    class Villian
    {
        function greetSpy(){}
    }
}

namespace foo\bar {
    class MyClass
    {
        function returnSomething(){}
        function echoSomething(){}
    }
}